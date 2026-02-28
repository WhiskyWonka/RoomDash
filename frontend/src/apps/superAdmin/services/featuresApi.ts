import { createResource } from "@/lib/api";
import type { Feature, CreateFeatureInput, UpdateFeatureInput } from "@/types/feature";

export const featuresApi = createResource<Feature, CreateFeatureInput, UpdateFeatureInput>("/api/features");