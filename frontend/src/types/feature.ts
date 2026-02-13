export interface Feature {
  id: string;
  slug: string;
  name: string;
  description: string;
  createdAt: string;
}

export interface CreateFeatureInput {
  slug: string;
  name: string;
  description: string;
}

export interface UpdateFeatureInput {
  slug: string;
  name: string;
  description: string;
}
