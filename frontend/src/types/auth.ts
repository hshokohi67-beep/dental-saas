export interface Tenant {
  id: string;
  name: string;
  slug: string;
  status: string;
}

export interface AuthenticatedUser {
  id: number;
  name: string;
  email: string;
  is_super_admin: boolean;
  tenant: Tenant | null;
}
