export type OrderStatus = 'pending' | 'for_delivery' | 'delivered' | 'canceled';

export type OrderItem = {
    id: number;
    order_id: number;
    product_id: number;
    quantity: number;
    price: number;
    product: {
        id: number;
        name: string;
        image: string | null;
        image_url: string | null;
    };
};

export type Order = {
    id: number;
    user_id: number;
    total: number;
    status: OrderStatus;
    items: OrderItem[];
    user?: {
        id: number;
        name: string;
        email: string;
    };
    created_at: string;
    updated_at: string;
};
