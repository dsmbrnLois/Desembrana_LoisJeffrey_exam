export type Product = {
    id: number;
    name: string;
    description: string | null;
    price: number;
    stocks: number;
    image: string | null;
    image_url: string | null;
    created_at: string;
    updated_at: string;
};

export type PaginatedProducts = {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};
