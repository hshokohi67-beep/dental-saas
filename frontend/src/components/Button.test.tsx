import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import userEvent from "@testing-library/user-event";
import { Button } from "./Button";

describe("Button", () => {
  it("renders its label and handles clicks", async () => {
    const onClick = vi.fn();
    render(<Button onClick={onClick}>ورود</Button>);

    const button = screen.getByRole("button", { name: "ورود" });
    await userEvent.click(button);

    expect(onClick).toHaveBeenCalledOnce();
  });

  it("disables interaction when disabled", () => {
    render(<Button disabled>ورود</Button>);

    expect(screen.getByRole("button", { name: "ورود" })).toBeDisabled();
  });
});
