import * as React from "react";
import { Slot } from "@radix-ui/react-slot";
import { type VariantProps, cva } from "class-variance-authority";
import { cn } from "@/lib/utils";
import "@/components/ui/8bit/styles/retro.css";

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-1.5 whitespace-nowrap text-sm font-medium transition-all active:translate-y-1 disabled:pointer-events-none disabled:opacity-50 relative cursor-pointer m-1.5 border-none",
  {
    variants: {
      font: {
        normal: "",
        retro: "retro",
      },
      variant: {
        // Restauramos bg-foreground como base y añadimos tus efectos
        default: "bg-foreground text-background hover:bg-foreground/90 hover:text-black focus:text-green-500 active:bg-black active:text-green-400",
        destructive: "bg-foreground text-background hover:bg-destructive hover:text-destructive-foreground",
        warning: "text-destructive-foreground hover:bg-red-600 hover:text-black",
        outline: "bg-transparent text-foreground hover:bg-foreground/10 active:translate-y-1",
        secondary: "bg-secondary text-secondary-foreground hover:bg-secondary/80",
        ghost: "hover:bg-accent hover:text-accent-foreground",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        // Recuperamos los sizes exactos del primer código
        default: "h-9 px-4 py-2 has-[>svg]:px-3",
        sm: "h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5",
        lg: "h-10 rounded-md px-6 has-[>svg]:px-4",
        icon: "size-9 mx-1 my-0",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
      font: "retro",
    },
  }
);

export interface BitButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, BitButtonProps>(
  ({ className, variant, size, font, asChild = false, children, ...props }, ref) => {
    const Comp = asChild ? Slot : "button";

    const variantClasses = buttonVariants({ variant, size, font });
    
    // Lógica para mostrar decoraciones basada en el código original
    const showDecorations = variant !== "ghost" && variant !== "link";
    const isIcon = size === "icon";

    return (
      <Comp
        ref={ref}
        // 2. Aquí unimos todo. El orden en cn() importa: 
        // Las clases de 'className' (como .test) deben ir AL FINAL para que ganen.
        className={cn(variantClasses, className)} 
        {...props}
      >
        <span className="relative z-10 flex items-center gap-1.5 text-inherit">
            {children}
        </span>

        {/* 1. BORDES PIXELADOS (Copiados exactamente del original) */}
        {showDecorations && !isIcon && (
          <>
            <div className="absolute -top-1.5 w-1/2 left-1.5 h-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute -top-1.5 w-1/2 right-1.5 h-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute -bottom-1.5 w-1/2 left-1.5 h-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute -bottom-1.5 w-1/2 right-1.5 h-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute top-0 left-0 size-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute top-0 right-0 size-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute bottom-0 left-0 size-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute bottom-0 right-0 size-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute top-1.5 -left-1.5 h-[calc(100%-12px)] w-1.5 bg-foreground dark:bg-ring" />
            <div className="absolute top-1.5 -right-1.5 h-[calc(100%-12px)] w-1.5 bg-foreground dark:bg-ring" />

            {/* 2. SOMBRAS INTERNAS (Copiadas exactamente del original) */}
            {/*variant !== "outline" && (
              <>
                <div className="absolute top-0 left-0 w-full h-1.5 bg-foreground/20 pointer-events-none" />
                <div className="absolute top-1.5 left-0 w-3 h-1.5 bg-foreground/20 pointer-events-none" />
                <div className="absolute bottom-0 left-0 w-full h-1.5 bg-foreground/20 pointer-events-none" />
                <div className="absolute bottom-1.5 right-0 w-3 h-1.5 bg-foreground/20 pointer-events-none" />
              </>
            )*/}
          </>
        )}

        {/* 3. DECORACIÓN PARA ICONOS (Copiada exactamente del original) */}
        {showDecorations && isIcon && (
          <>
            <div className="absolute top-0 left-0 w-full h-[5px] md:h-1.5 bg-foreground dark:bg-ring pointer-events-none" />
            <div className="absolute bottom-0 w-full h-[5px] md:h-1.5 bg-foreground dark:bg-ring pointer-events-none" />
            <div className="absolute top-1 -left-1 w-[5px] md:w-1.5 h-1/2 bg-foreground dark:bg-ring pointer-events-none" />
            <div className="absolute bottom-1 -left-1 w-[5px] md:w-1.5 h-1/2 bg-foreground dark:bg-ring pointer-events-none" />
            <div className="absolute top-1 -right-1 w-[5px] md:w-1.5 h-1/2 bg-foreground dark:bg-ring pointer-events-none" />
            <div className="absolute bottom-1 -right-1 w-[5px] md:w-1.5 h-1/2 bg-foreground dark:bg-ring pointer-events-none" />
          </>
        )}
      </Comp>
    );
  }
);

Button.displayName = "BitButton";

export { Button };