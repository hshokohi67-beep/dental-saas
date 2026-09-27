<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class Dental_Jalali
 * تبدیل تاریخ بین شمسی و میلادی — بدون وابستگی به کتابخانه خارجی
 * الگوریتم بر اساس تقویم جلالی (الگوریتم Borkowski)
 */
class Dental_Jalali {

    // ─── ثابت‌های تقویم ──────────────────────────────────────────────────────

    private const JALALI_MONTHS_FA = [
        1  => 'فروردین',
        2  => 'اردیبهشت',
        3  => 'خرداد',
        4  => 'تیر',
        5  => 'مرداد',
        6  => 'شهریور',
        7  => 'مهر',
        8  => 'آبان',
        9  => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند',
    ];

    private const JALALI_DAYS_FA = [
        'Saturday'  => 'شنبه',
        'Sunday'    => 'یک‌شنبه',
        'Monday'    => 'دوشنبه',
        'Tuesday'   => 'سه‌شنبه',
        'Wednesday' => 'چهارشنبه',
        'Thursday'  => 'پنج‌شنبه',
        'Friday'    => 'جمعه',
    ];

    // ─── متدهای اصلی ─────────────────────────────────────────────────────────

    /**
     * تبدیل تاریخ میلادی به شمسی
     *
     * @param string|int $date   تاریخ میلادی (Y-m-d) یا timestamp
     * @param string     $format فرمت خروجی (Y/m/d، d F Y، ...)
     * @return string
     */
    public static function to_jalali( string|int $date, string $format = 'Y/m/d' ): string {
        if ( is_int( $date ) ) {
            $timestamp = $date;
        } else {
            $timestamp = strtotime( $date );
        }

        if ( false === $timestamp ) {
            return '';
        }

        $g_y = (int) gmdate( 'Y', $timestamp );
        $g_m = (int) gmdate( 'n', $timestamp );
        $g_d = (int) gmdate( 'j', $timestamp );

        [ $j_y, $j_m, $j_d ] = self::gregorian_to_jalali( $g_y, $g_m, $g_d );

        return self::format_jalali( $j_y, $j_m, $j_d, $format, $timestamp );
    }

    /**
     * تبدیل تاریخ شمسی به میلادی
     *
     * @param string $jalali_date تاریخ شمسی (1403/06/15)
     * @return string تاریخ میلادی (Y-m-d)
     */
    public static function to_gregorian( string $jalali_date ): string {
        $parts = explode( '/', str_replace( '-', '/', $jalali_date ) );

        if ( count( $parts ) !== 3 ) {
            return '';
        }

        [ $j_y, $j_m, $j_d ] = array_map( 'intval', $parts );
        [ $g_y, $g_m, $g_d ] = self::jalali_to_gregorian( $j_y, $j_m, $j_d );

        return sprintf( '%04d-%02d-%02d', $g_y, $g_m, $g_d );
    }

    /**
     * تاریخ امروز به شمسی
     */
    public static function today( string $format = 'Y/m/d' ): string {
        // رفع باگ مهم: قبلاً از time() خام (UTC) استفاده می‌شد که تایم‌زون
        // سایت (ایران، UTC+3:30) رو درنظر نمی‌گرفت — نتیجه‌ش این بود که
        // توی بازه‌ی حدوداً ۳.۵ ساعته‌ی بعد از نیمه‌شب ایران (وقتی ایران
        // از نظر تاریخ از UTC جلوتره)، «امروز» یه روز عقب‌تر محاسبه
        // می‌شد. current_time() وردپرسی، تایم‌زون تنظیم‌شده‌ی سایت رو
        // درست اعمال می‌کنه.
        return self::to_jalali( current_time( 'timestamp' ), $format );
    }

    /**
     * افزودن روز به تاریخ شمسی
     *
     * @param string $jalali_date تاریخ شمسی
     * @param int    $days        تعداد روز (مثبت یا منفی)
     * @return string
     */
    public static function add_days( string $jalali_date, int $days ): string {
        $gregorian = self::to_gregorian( $jalali_date );
        if ( empty( $gregorian ) ) {
            return '';
        }

        $timestamp = strtotime( $gregorian . " +{$days} days" );
        return self::to_jalali( $timestamp );
    }

    /**
     * افزودن ماه به تاریخ شمسی
     *
     * @param string $jalali_date تاریخ شمسی
     * @param int    $months      تعداد ماه
     * @return string
     */
    public static function add_months( string $jalali_date, int $months ): string {
        $parts = explode( '/', $jalali_date );
        if ( count( $parts ) !== 3 ) {
            return '';
        }

        $j_y = (int) $parts[0];
        $j_m = (int) $parts[1];
        $j_d = (int) $parts[2];

        $j_m += $months;

        // مدیریت سرریز ماه‌ها
        while ( $j_m > 12 ) {
            $j_m -= 12;
            $j_y++;
        }
        while ( $j_m < 1 ) {
            $j_m += 12;
            $j_y--;
        }

        // بررسی تعداد روزهای ماه
        $days_in_month = self::days_in_jalali_month( $j_y, $j_m );
        if ( $j_d > $days_in_month ) {
            $j_d = $days_in_month;
        }

        return sprintf( '%04d/%02d/%02d', $j_y, $j_m, $j_d );
    }

    /**
     * محاسبه تاریخ‌های سررسید اقساط
     *
     * @param string $start_date_jalali تاریخ شروع شمسی
     * @param int    $count             تعداد اقساط
     * @param int    $interval_months   فاصله بین اقساط (پیش‌فرض: ۱ ماه)
     * @return array آرایه‌ای از تاریخ‌های شمسی
     */
    public static function get_installment_dates(
        string $start_date_jalali,
        int    $count,
        int    $interval_months = 1
    ): array {
        $dates = [];

        // رفع باگ مهم: قبلاً قسط اول = همون روز ساخت پلن بود (که یعنی
        // همون روزی که پیش‌پرداخت گرفته می‌شه) — این اشتباه بود، چون
        // بیمار همون روز که پیش‌پرداخت داده، دیگه نباید بلافاصله یه
        // قسط دیگه هم سررسیدش برسه. الان همه‌ی اقساط (حتی اولی) در
        // آینده‌ان، هرکدوم یه فاصله بیشتر از قبلی.
        for ( $i = 0; $i < $count; $i++ ) {
            $dates[] = self::add_months( $start_date_jalali, ( $i + 1 ) * $interval_months );
        }

        return $dates;
    }

    /**
     * مقایسه دو تاریخ شمسی
     *
     * @param string $date1
     * @param string $date2
     * @return int  -1، 0، یا 1
     */
    public static function compare( string $date1, string $date2 ): int {
        $g1 = self::to_gregorian( $date1 );
        $g2 = self::to_gregorian( $date2 );

        $t1 = strtotime( $g1 );
        $t2 = strtotime( $g2 );

        return $t1 <=> $t2;
    }

    /**
     * بررسی گذشته بودن تاریخ شمسی
     */
    public static function is_past( string $jalali_date ): bool {
        return self::compare( $jalali_date, self::today() ) < 0;
    }

    /**
     * تعداد روزهای مانده تا تاریخ شمسی
     */
    public static function days_until( string $jalali_date ): int {
        $gregorian = self::to_gregorian( $jalali_date );
        $diff      = strtotime( $gregorian ) - mktime( 0, 0, 0 );
        return (int) ceil( $diff / DAY_IN_SECONDS );
    }

    /**
     * نام ماه شمسی به فارسی
     */
    public static function month_name( int $month_number ): string {
        return self::JALALI_MONTHS_FA[ $month_number ] ?? '';
    }

    /**
     * نام روز هفته به فارسی
     */
    public static function day_name( string|int $date ): string {
        if ( is_string( $date ) ) {
            // اگر شمسی است، اول به میلادی تبدیل کن
            if ( preg_match( '/^\d{4}\/\d{2}\/\d{2}$/', $date ) ) {
                $date = strtotime( self::to_gregorian( $date ) );
            } else {
                $date = strtotime( $date );
            }
        }

        $day_en = gmdate( 'l', $date );
        return self::JALALI_DAYS_FA[ $day_en ] ?? '';
    }

    /**
     * تعداد روزهای ماه شمسی
     */
    public static function days_in_jalali_month( int $year, int $month ): int {
        if ( $month <= 6 ) {
            return 31;
        }
        if ( $month <= 11 ) {
            return 30;
        }
        // اسفند — بررسی کبیسه
        return self::is_jalali_leap_year( $year ) ? 30 : 29;
    }

    /**
     * بررسی سال کبیسه شمسی
     */
    public static function is_jalali_leap_year( int $year ): bool {
        $leaps = [1, 5, 9, 13, 17, 22, 26, 30];
        return in_array( ( ( $year - ( $year > 0 ? 474 : 473 ) ) % 2820 + 474 + 38 ) * 682 % 2816, $leaps, true );
    }

    /**
     * تولید آرایه روزهای ماه برای تقویم (با اطلاعات ساختاریافته)
     *
     * @param int $year  سال شمسی
     * @param int $month ماه شمسی
     * @return array
     */
    public static function get_month_calendar( int $year, int $month ): array {
        $first_day_gregorian = self::to_gregorian( sprintf( '%04d/%02d/01', $year, $month ) );
        $first_day_of_week   = (int) gmdate( 'w', strtotime( $first_day_gregorian ) );

        // تبدیل به سیستم شنبه=0
        $start_offset = ( $first_day_of_week + 1 ) % 7;

        $days_count = self::days_in_jalali_month( $year, $month );

        $calendar = [
            'year'         => $year,
            'month'        => $month,
            'month_name'   => self::JALALI_MONTHS_FA[ $month ],
            'days_count'   => $days_count,
            'start_offset' => $start_offset,
            'days'         => [],
        ];

        for ( $d = 1; $d <= $days_count; $d++ ) {
            $jalali_date = sprintf( '%04d/%02d/%02d', $year, $month, $d );
            $gregorian   = self::to_gregorian( $jalali_date );

            $calendar['days'][] = [
                'day'          => $d,
                'jalali'       => $jalali_date,
                'gregorian'    => $gregorian,
                'day_of_week'  => (int) gmdate( 'w', strtotime( $gregorian ) ),
                'day_name'     => self::day_name( $jalali_date ),
                'is_today'     => $jalali_date === self::today(),
                'is_past'      => self::is_past( $jalali_date ),
                'is_holiday'   => self::is_holiday( $gregorian ),
            ];
        }

        return $calendar;
    }

    /**
     * بررسی تعطیل بودن روز (جمعه یا تعطیلات رسمی)
     */
    public static function is_holiday( string $gregorian_date ): bool {
        $day_of_week = gmdate( 'l', strtotime( $gregorian_date ) );
        return $day_of_week === 'Friday';
    }

    /**
     * فرمت‌بندی خروجی تاریخ شمسی
     */
    private static function format_jalali(
        int    $j_y,
        int    $j_m,
        int    $j_d,
        string $format,
        int    $timestamp
    ): string {
        $replacements = [
            'Y' => sprintf( '%04d', $j_y ),
            'y' => substr( sprintf( '%04d', $j_y ), -2 ),
            'm' => sprintf( '%02d', $j_m ),
            'n' => (string) $j_m,
            'd' => sprintf( '%02d', $j_d ),
            'j' => (string) $j_d,
            'F' => self::JALALI_MONTHS_FA[ $j_m ] ?? '',
            'M' => mb_substr( self::JALALI_MONTHS_FA[ $j_m ] ?? '', 0, 3 ),
            'l' => self::JALALI_DAYS_FA[ gmdate( 'l', $timestamp ) ] ?? '',
            'D' => mb_substr( self::JALALI_DAYS_FA[ gmdate( 'l', $timestamp ) ] ?? '', 0, 1 ),
            'H' => gmdate( 'H', $timestamp ),
            'i' => gmdate( 'i', $timestamp ),
            's' => gmdate( 's', $timestamp ),
            'G' => gmdate( 'G', $timestamp ),
            'A' => gmdate( 'H', $timestamp ) < 12 ? 'ق.ظ' : 'ب.ظ',
            'N' => (string) ( ( (int) gmdate( 'w', $timestamp ) + 1 ) % 7 + 1 ),
            'L' => self::is_jalali_leap_year( $j_y ) ? '1' : '0',
            't' => (string) self::days_in_jalali_month( $j_y, $j_m ),
        ];

        return str_replace(
            array_keys( $replacements ),
            array_values( $replacements ),
            $format
        );
    }

    // ─── الگوریتم‌های تبدیل ──────────────────────────────────────────────────

    /**
     * تبدیل میلادی به شمسی (الگوریتم اصلی)
     *
     * @return array [year, month, day]
     */
    public static function gregorian_to_jalali( int $g_y, int $g_m, int $g_d ): array {
        $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $gd = $g_d - 1;

        $g_day_no = 365 * $gy
            + (int) ( ( $gy + 3 ) / 4 )
            - (int) ( ( $gy + 99 ) / 100 )
            + (int) ( ( $gy + 399 ) / 400 );

        for ( $i = 0; $i < $gm; $i++ ) {
            $g_day_no += $g_days_in_month[ $i ];
        }

        if ( $gm > 1 && ( ( $gy % 4 === 0 && $gy % 100 !== 0 ) || ( $gy % 400 === 0 ) ) ) {
            $g_day_no++;
        }

        $g_day_no += $gd;

        $j_day_no = $g_day_no - 79;

        $j_np = (int) ( $j_day_no / 12053 );
        $j_day_no %= 12053;

        $jy = 979 + 33 * $j_np + 4 * (int) ( $j_day_no / 1461 );
        $j_day_no %= 1461;

        if ( $j_day_no >= 366 ) {
            $jy += (int) ( ( $j_day_no - 1 ) / 365 );
            $j_day_no = ( $j_day_no - 1 ) % 365;
        }

        for ( $i = 0; $i < 11 && $j_day_no >= $j_days_in_month[ $i ]; $i++ ) {
            $j_day_no -= $j_days_in_month[ $i ];
        }

        return [ $jy, $i + 1, $j_day_no + 1 ];
    }

    /**
     * تبدیل شمسی به میلادی (الگوریتم اصلی)
     *
     * @return array [year, month, day]
     */
    public static function jalali_to_gregorian( int $j_y, int $j_m, int $j_d ): array {
        $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $jy = $j_y - 979;
        $jm = $j_m - 1;
        $jd = $j_d - 1;

        $j_day_no = 365 * $jy + (int) ( $jy / 33 ) * 8 + (int) ( ( $jy % 33 + 3 ) / 4 );

        for ( $i = 0; $i < $jm; $i++ ) {
            $j_day_no += $j_days_in_month[ $i ];
        }

        $j_day_no += $jd;

        $g_day_no = $j_day_no + 79;

        $gy = 1600 + 400 * (int) ( $g_day_no / 146097 );
        $g_day_no %= 146097;

        $leap = true;
        if ( $g_day_no >= 36525 ) {
            $g_day_no--;
            $gy += 100 * (int) ( $g_day_no / 36524 );
            $g_day_no %= 36524;

            if ( $g_day_no >= 365 ) {
                $g_day_no++;
            } else {
                $leap = false;
            }
        }

        $gy += 4 * (int) ( $g_day_no / 1461 );
        $g_day_no %= 1461;

        if ( $g_day_no >= 366 ) {
            $leap = false;
            $g_day_no--;
            $gy += (int) ( $g_day_no / 365 );
            $g_day_no %= 365;
        }

        for ( $i = 0; $g_day_no >= $g_days_in_month[ $i ] + ( $i === 1 && $leap ? 1 : 0 ); $i++ ) {
            $g_day_no -= $g_days_in_month[ $i ] + ( $i === 1 && $leap ? 1 : 0 );
        }

        return [ $gy, $i + 1, $g_day_no + 1 ];
    }

    /**
     * تبدیل اعداد به فارسی
     */
    public static function to_persian_digits( string $string ): string {
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace( $english, $persian, $string );
    }

    /**
     * تبدیل اعداد فارسی/عربی به انگلیسی
     */
    public static function to_english_digits( string $string ): string {
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $english = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
        return str_replace( $persian, $english, $string );
    }
}
