(function(){
var id  = window._dcId;
var api = window._dcApi;
var nc  = window._dcNc;
var rd  = window._dcRd;
var ex  = window._dcEx;
var cc  = window._dcCc;
var dv  = window._dcDv;
var ot  = window._dcOt;
var mob = "";
var ti  = null;
var ri  = null;

function g(s){ return document.getElementById(id+"-"+s); }

function show(s){
    ["s1","s2","s3","s4"].forEach(function(x){
        var e = g(x);
        if(e) e.style.display = x===s ? "block" : "none";
    });
}

function se(s, m){
    var e = g(s);
    if(e) e.textContent = m;
}

// تبدیل اعداد فارسی/عربی به انگلیسی
function toEN(s){
    return (s||"").replace(/[۰-۹]/g, function(d){ return d.charCodeAt(0)-1776; })
                  .replace(/[٠-٩]/g, function(d){ return d.charCodeAt(0)-1632; });
}

// دریافت کد ۶ رقمی از باکس‌ها
function getCode(){
    var r = "";
    // پشتیبانی از هر دو کلاس قدیم و جدید
    var boxes = document.querySelectorAll("."+id+"-otp-box, ."+id+"-box");
    boxes.forEach(function(b){ r += b.value; });
    return r;
}

// ─── تایمر انقضا ─────────────────────────────────────────────
function startTimer(){
    var l = ex;
    clearInterval(ti);
    ti = setInterval(function(){
        l--;
        var e = g("tmr");
        if(e){
            var m = Math.floor(l/60);
            var s = l%60;
            e.textContent = (m<10?"0":"")+m+":"+(s<10?"0":"")+s;
        }
        if(l <= 0){
            clearInterval(ti);
            se("e2","کد منقضی شده، لطفاً مجدداً ارسال کنید");
        }
    }, 1000);
}

// ─── تایمر ارسال مجدد ────────────────────────────────────────
function startResend(){
    var l  = cc;
    var rw = g("rw"), rb = g("rb"), rn = g("rn");
    if(rw) rw.style.display = "inline";
    if(rb) rb.style.display = "none";
    clearInterval(ri);
    ri = setInterval(function(){
        l--;
        if(rn) rn.textContent = l;
        if(l <= 0){
            clearInterval(ri);
            if(rw) rw.style.display = "none";
            if(rb) rb.style.display = "inline";
        }
    }, 1000);
}

// ─── راه‌اندازی باکس‌های OTP ─────────────────────────────────
function initBoxes(){
    var bs = document.querySelectorAll("."+id+"-otp-box, ."+id+"-box");
    if(!bs.length) return;

    bs.forEach(function(b, i){
        // پاک‌سازی eventها (در صورت فراخوانی مجدد)
        var newB = b.cloneNode(true);
        b.parentNode.replaceChild(newB, b);
    });

    // مجدداً select کن بعد از clone
    bs = document.querySelectorAll("."+id+"-otp-box, ."+id+"-box");

    bs.forEach(function(b, i){

        // ─── ورود عدد ────────────────────────────────────────
        b.addEventListener("input", function(){
            var v = toEN(this.value).replace(/\D/g, "");
            this.value = v ? v[0] : ""; // فقط یک کاراکتر

            if(this.value){
                this.classList.add("filled");
                this.style.borderColor = "#2ECC9A";
                this.style.background  = "#E8FAF4";
                // انتقال به باکس بعدی
                if(i < bs.length-1) bs[i+1].focus();
            } else {
                this.classList.remove("filled");
                this.style.borderColor = "";
                this.style.background  = "";
            }

            // تأیید خودکار اگه همه پر شد
            if(getCode().length === 6){
                setTimeout(doVerify, 200);
            }
        });

        // ─── Backspace ────────────────────────────────────────
        b.addEventListener("keydown", function(e){
            if(e.key === "Backspace"){
                if(!this.value && i > 0){
                    bs[i-1].value = "";
                    bs[i-1].classList.remove("filled");
                    bs[i-1].style.borderColor = "";
                    bs[i-1].style.background  = "";
                    bs[i-1].focus();
                }
            }
            // پشتیبانی از Enter
            if(e.key === "Enter" && getCode().length === 6){
                doVerify();
            }
        });

        // ─── کلیک روی باکس: انتخاب محتوا ───────────────────
        b.addEventListener("focus", function(){
            this.select();
        });

        // ─── Paste: جایگذاری کل کد ───────────────────────────
        b.addEventListener("paste", function(e){
            e.preventDefault();
            var p = toEN(e.clipboardData.getData("text")).replace(/\D/g,"");
            bs.forEach(function(x, j){
                x.value = p[j] || "";
                if(x.value){
                    x.classList.add("filled");
                    x.style.borderColor = "#2ECC9A";
                    x.style.background  = "#E8FAF4";
                } else {
                    x.classList.remove("filled");
                    x.style.borderColor = "";
                    x.style.background  = "";
                }
            });
            if(p.length >= 6) setTimeout(doVerify, 200);
        });
    });
}

// ─── ارسال OTP ───────────────────────────────────────────────
function doSend(){
    se("e1","");
    var inp = g("mob");
    var m   = toEN((inp ? inp.value : "").trim());

    if(!/^09[0-9]{9}$/.test(m)){
        se("e1","شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود");
        if(inp) inp.focus();
        return;
    }

    mob = m;
    var btn = g("btn0");
    if(btn){ btn.disabled=true; btn.textContent="در حال ارسال..."; }

    post("/auth/send-otp", {mobile:m, purpose:"login"}, function(r){
        if(btn){ btn.disabled=false; btn.textContent="دریافت کد تأیید"; }

        if(r && r.success){
            var n = g("num");
            if(n) n.textContent = m;
            show("s2");
            initBoxes();
            startTimer();
            startResend();
            // فوکوس روی اولین باکس
            setTimeout(function(){
                var first = document.querySelector("."+id+"-otp-box, ."+id+"-box");
                if(first) first.focus();
            }, 150);
            if(dv) console.warn("[DC Dev] OTP:", r.dev_otp || ot);
        } else {
            se("e1", (r && r.message) ? r.message : "خطا در ارسال کد");
        }
    });
}

// ─── تأیید OTP ───────────────────────────────────────────────
function doVerify(){
    var c = getCode();
    if(c.length < 6){
        se("e2","لطفاً کد ۶ رقمی را کامل وارد کنید");
        return;
    }

    se("e2","");
    var btn = g("btn1");
    if(btn){ btn.disabled=true; btn.textContent="در حال تأیید..."; }

    post("/auth/verify-otp", {mobile:mob, otp:c, purpose:"login"}, function(r){
        if(btn){ btn.disabled=false; btn.textContent="تأیید و ورود"; }

        if(r && r.success){
            clearInterval(ti);
            clearInterval(ri);
            show("s3");
            setTimeout(function(){
                show("s4");
                var ok = g("ok");
                if(ok) ok.textContent = "خوش آمدید، " + (r.display_name || "") + "!";
                setTimeout(function(){
                    window.location.href = r.redirect_url || rd;
                }, 1800);
            }, 700);
        } else {
            // پاک کردن باکس‌ها
            document.querySelectorAll("."+id+"-otp-box, ."+id+"-box").forEach(function(b){
                b.value = "";
                b.classList.remove("filled");
                b.style.borderColor = "#E05252";
                b.style.background  = "#FDEAEA";
            });
            se("e2", (r && r.message) ? r.message : "کد وارد شده اشتباه است");
            // ریست رنگ بعد از ۱ ثانیه
            setTimeout(function(){
                document.querySelectorAll("."+id+"-otp-box, ."+id+"-box").forEach(function(b){
                    b.style.borderColor = "";
                    b.style.background  = "";
                });
                // فوکوس روی اولین باکس
                var first = document.querySelector("."+id+"-otp-box, ."+id+"-box");
                if(first) first.focus();
            }, 1000);
        }
    });
}

// ─── Ajax ────────────────────────────────────────────────────
function post(path, data, cb){
    var x = new XMLHttpRequest();
    x.open("POST", api+path);
    x.setRequestHeader("Content-Type","application/json");
    x.setRequestHeader("X-WP-Nonce", nc);
    x.onload = function(){
        try{
            var r = JSON.parse(x.responseText);
            if(dv) console.log("[DC]", path, r);
            cb(r);
        } catch(e){
            console.error("[DC] parse error:", x.responseText);
            cb({success:false, message:"خطای شبکه"});
        }
    };
    x.onerror = function(){ cb({success:false, message:"خطای اتصال"}); };
    x.send(JSON.stringify(data));
}

// ─── Event Listeners ─────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function(){
    var b0 = g("btn0");
    if(b0) b0.addEventListener("click", doSend);

    var b1 = g("btn1");
    if(b1) b1.addEventListener("click", doVerify);

    var rb = g("rb");
    if(rb) rb.addEventListener("click", function(){
        post("/auth/send-otp", {mobile:mob, purpose:"login"}, function(r){
            if(r && r.success){
                startTimer();
                startResend();
                // پاک کردن باکس‌ها
                document.querySelectorAll("."+id+"-otp-box, ."+id+"-box").forEach(function(b){
                    b.value="";
                    b.classList.remove("filled");
                    b.style.borderColor="";
                    b.style.background="";
                });
                se("e2","");
                setTimeout(function(){
                    var first = document.querySelector("."+id+"-otp-box, ."+id+"-box");
                    if(first) first.focus();
                }, 100);
                if(dv) console.warn("[DC Dev] OTP:", r.dev_otp || ot);
            }
        });
    });

    var bk = g("back");
    if(bk) bk.addEventListener("click", function(){
        clearInterval(ti);
        clearInterval(ri);
        show("s1");
        se("e2","");
        var inp = g("mob");
        if(inp) inp.focus();
    });

    var mi = g("mob");
    if(mi) mi.addEventListener("keydown", function(e){
        if(e.key === "Enter") doSend();
    });

    if(dv) console.log("[DC] Dev Mode ON, OTP:", ot);
});
})();
