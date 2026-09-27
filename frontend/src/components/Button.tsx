import { ButtonHTMLAttributes } from "react";

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: "primary" | "ghost";
};

export function Button({ variant = "primary", className = "", ...props }: ButtonProps) {
  const base =
    "inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed";
  const variants = {
    primary: "bg-primary text-primary-foreground hover:opacity-90",
    ghost: "bg-transparent text-foreground border border-border hover:bg-black/5",
  };

  return <button className={`${base} ${variants[variant]} ${className}`} {...props} />;
}
