/** Snappy order statuses (must match SnappyOrderRepositoryInterface::ALLOWED_STATUSES). */
export const ORDER_FULFILLMENT_STATUS_SEQUENCE = [
  'pending',
  'paid',
  'placed',
  'shipped-to-facility',
  'arrived-at-facility',
  'shipped-to-destination-country',
  'arrived-at-destination-country',
  'arrived-at-destination-facility',
  'ready-for-pickup',
  'delivered',
  'cancelled',
];

export const ORDER_STATUS_FILTERS = [...ORDER_FULFILLMENT_STATUS_SEQUENCE];
