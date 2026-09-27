export interface Staff {
  id: string;
  title: string | null;
  status: string;
  branch_id: string | null;
  user: {
    id: number;
    name: string;
    email: string;
    roles: string[];
  };
}
