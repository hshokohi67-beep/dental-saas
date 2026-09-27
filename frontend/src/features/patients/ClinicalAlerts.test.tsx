import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { ClinicalAlerts } from "./ClinicalAlerts";

describe("ClinicalAlerts", () => {
  it("renders nothing when there are no alerts", () => {
    const { container } = render(<ClinicalAlerts alerts={[]} />);

    expect(container).toBeEmptyDOMElement();
  });

  it("lists every alert with its label and message", () => {
    const { container } = render(
      <ClinicalAlerts
        alerts={[
          { source: "medical_condition", label: "دیابت", message: "کنترل قند خون قبل از درمان" },
          { source: "allergy", label: "پنی‌سیلین", message: "حساسیت شدید به پنی‌سیلین؛ در تجویز دارو احتیاط شود." },
        ]}
      />,
    );

    expect(screen.getByText("هشدارهای بالینی")).toBeInTheDocument();
    expect(container.textContent).toContain("کنترل قند خون قبل از درمان");
    expect(container.textContent).toContain("حساسیت شدید به پنی‌سیلین");
  });
});
