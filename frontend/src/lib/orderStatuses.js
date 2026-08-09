/** Snappy order statuses (must match API / Postman). */
export const ORDER_FULFILLMENT_STATUS_SEQUENCE = [
  'pending',
  'paid',
  'assigned',
  'completed',
  'cancelled',
];

export const ORDER_STATUS_FILTERS = [...ORDER_FULFILLMENT_STATUS_SEQUENCE];
