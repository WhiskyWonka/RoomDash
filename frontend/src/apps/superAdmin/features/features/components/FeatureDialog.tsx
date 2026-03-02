import { useEffect } from "react";
import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import type { Feature } from "@/types/feature";
import { DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/8bit/dialog";
import { Button } from "@/components/ui/8bit/button";
import { Input } from "@/components/ui/8bit/input";
import { Field, FieldLabel, FieldContent, FieldDescription, FieldError } from "@/components/ui/shadcn/field";

import { Dialog } from "@/components/ui/8bit/dialog"

const featureSchema = z.object({
  name: z.string().min(1, "Name is required").max(255),
  slug: z.string().min(1, "Name is required").max(255),
  description: z
    .string()
    .min(1, "Domain is required")
    .max(255)
    .regex(/^[a-z0-9-]+$/, "Only lowercase letters, numbers, and hyphens"),
});

type FeatureFormValues = z.infer<typeof featureSchema>;

interface Props {
  open: boolean;
  feature: Feature | null;
  onClose: () => void;
  onSubmit: (name: string, slug: string, description: string) => void;
}

export function FeatureDialog({ open, feature, onClose, onSubmit }: Props) {
  const form = useForm<FeatureFormValues>({
    resolver: zodResolver(featureSchema),
    defaultValues: { name: "", description: "" , slug: ""},
  });

  useEffect(() => {
    if (open) {
      form.reset({
        slug: feature?.slug ?? "",
        name: feature?.name ?? "",
        description: feature?.description ?? "",
      });
    }
  }, [open, feature, form]);

  const handleSubmit = (values: FeatureFormValues) => {
    onSubmit(values.name, values.slug, values.description);
  };

  return (
    <Dialog open={open} onOpenChange={(v) => { if (!v) onClose(); }}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle className="mb-8">{feature ? "Edit Feature" : "Create Feature"}</DialogTitle>
          <DialogDescription className="text-xs">
            {feature ? "Update the feature details below." : "Fill in the details to create a new feature."}
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={form.handleSubmit(handleSubmit)} className="grid gap-4 py-4">
          <Controller
            name="name"
            control={form.control}
            render={({ field, fieldState }) => (
              <Field data-invalid={fieldState.invalid || undefined}>
                <FieldContent>
                  <FieldLabel htmlFor="name">Name</FieldLabel>
                  <Input id="name" placeholder="My Feature" {...field} aria-invalid={fieldState.invalid} />
                  <FieldDescription>The display name for this feature.</FieldDescription>
                  {fieldState.error && <FieldError errors={[fieldState.error]} />}
                </FieldContent>
              </Field>
            )}
          />
          <Controller
            name="description"
            control={form.control}
            render={({ field, fieldState }) => (
              <Field data-invalid={fieldState.invalid || undefined}>
                <FieldContent>
                  <FieldLabel htmlFor="description">Domain</FieldLabel>
                  <Input id="description" placeholder="my-feature" {...field} aria-invalid={fieldState.invalid} />
                  <FieldDescription>The subdescription used to access this feature.</FieldDescription>
                  {fieldState.error && <FieldError errors={[fieldState.error]} />}
                </FieldContent>
              </Field>
            )}
          />
          <DialogFooter className="gap-4">
            <Button type="button" variant="outline" onClick={onClose}>Cancel</Button>
            <Button type="submit">{feature ? "Save" : "Create"}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
