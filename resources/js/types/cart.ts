export type CartItem = {
    id: number;
    user_id: number;
    product_id: number;
    quantity: number;
    product: {
        id: number;
        name: string;
        price: number;
        stocks: number;
        image: string | null;
        image_url: string | null;
    };
    created_at: string;
    updated_at: string;
};

export type CartData = {
    items: CartItem[];
    total: number;
    count: number;
};
