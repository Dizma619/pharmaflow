import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {

  // ======================
  // STATE
  // ======================
  const items = ref(
    localStorage.getItem('cart')
      ? JSON.parse(localStorage.getItem('cart'))
      : []
  )

  // Guest checkout info
  const guestInfo = ref({
    customer_name: '',
    phone: '',
    address: '',
    city: '',
    province: '',
    postal_code: '',
    notes: '',
    shipping_method: 'standard',
    payment_method: 'cod',
  })

  const discountAmount = ref(0)

  // ======================
  // COMPUTED
  // ======================

  // Lama (biar tidak rusak)
  const cartTotal = computed(() =>
    items.value.reduce(
      (total, item) => total + item.subtotal,
      0
    )
  )

  const cartCount = computed(() =>
    items.value.reduce(
      (total, item) => total + item.quantity,
      0
    )
  )

  // Baru
  const shippingCost = computed(() => {
    const costs = {
      standard: 10000,
      express: 25000,
      same_day: 50000,
    }

    return (
      costs[
        guestInfo.value.shipping_method
      ] || 10000
    )
  })

  const grandTotal = computed(() => {
    return (
      cartTotal.value +
      shippingCost.value -
      discountAmount.value
    )
  })

  // ======================
  // CART ACTIONS
  // ======================

  /**
   * Add item (lama tetap dipakai)
   */
  const addItem = (
    medicine,
    quantity = 1
  ) => {

    const existingItem =
      items.value.find(
        item =>
          item.medicine_id ===
          medicine.id
      )

    if (existingItem) {

      existingItem.quantity +=
        quantity

      existingItem.subtotal =
        existingItem.unit_price *
        existingItem.quantity

    } else {

      items.value.push({
        medicine_id:
          medicine.id,

        medicine:
          medicine,

        quantity,

        unit_price:
          medicine.selling_price,

        subtotal:
          medicine.selling_price *
          quantity,
      })
    }

    saveCart()
  }

  /**
   * Alias baru
   */
  const addToCart = addItem

  /**
   * Remove item
   */
  const removeItem = (
    medicineId
  ) => {

    items.value =
      items.value.filter(
        item =>
          item.medicine_id !==
          medicineId
      )

    saveCart()
  }

  const removeFromCart =
    removeItem

  /**
   * Update quantity
   */
  const updateQuantity = (
    medicineId,
    quantity
  ) => {

    const item =
      items.value.find(
        item =>
          item.medicine_id ===
          medicineId
      )

    if (item) {

      if (quantity <= 0) {
        removeItem(
          medicineId
        )
      } else {

        item.quantity =
          quantity

        item.subtotal =
          item.unit_price *
          quantity

        saveCart()
      }
    }
  }

  /**
   * Clear cart
   */
  const clearCart = () => {
    items.value = []
    discountAmount.value = 0

    localStorage.removeItem(
      'cart'
    )
  }

  // ======================
  // GUEST INFO
  // ======================

  const setGuestInfo = (
    info
  ) => {

    guestInfo.value = {
      ...guestInfo.value,
      ...info,
    }

    saveCart()
  }

  const updateGuestInfo = (
    field,
    value
  ) => {

    guestInfo.value[
      field
    ] = value

    saveCart()
  }

  const validateGuestInfo =
    () => {

      const required = [
        'customer_name',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
      ]

      const errors = []

      for (
        const field
        of required
      ) {

        if (
          !guestInfo.value[
            field
          ]
        ) {
          errors.push(
            field
          )
        }
      }

      return {
        valid:
          errors.length ===
          0,

        errors,
      }
    }

  // ======================
  // DISCOUNT
  // ======================

  const setDiscount = (
    amount
  ) => {

    discountAmount.value =
      Math.max(
        0,
        amount
      )

    saveCart()
  }

  // ======================
  // STORAGE
  // ======================

  const saveCart = () => {

    localStorage.setItem(
      'cart',
      JSON.stringify(
        items.value
      )
    )

    localStorage.setItem(
      'guestInfo',
      JSON.stringify(
        guestInfo.value
      )
    )

    localStorage.setItem(
      'discountAmount',
      discountAmount.value
    )
  }

  const loadCart = () => {

    const savedGuest =
      localStorage.getItem(
        'guestInfo'
      )

    if (savedGuest) {
      guestInfo.value =
        JSON.parse(
          savedGuest
        )
    }

    const savedDiscount =
      localStorage.getItem(
        'discountAmount'
      )

    if (
      savedDiscount
    ) {

      discountAmount.value =
        Number(
          savedDiscount
        )
    }
  }

  /**
   * Order data
   */
  const getOrderData =
    () => {

      return {
        items:
          items.value.map(
            item => ({
              medicine_id:
                item.medicine_id,

              quantity:
                item.quantity,
            })
          ),

        customer_name:
          guestInfo.value
            .customer_name,

        phone:
          guestInfo.value
            .phone,

        address:
          guestInfo.value
            .address,

        city:
          guestInfo.value
            .city,

        province:
          guestInfo.value
            .province,

        postal_code:
          guestInfo.value
            .postal_code,

        notes:
          guestInfo.value
            .notes,

        shipping_method:
          guestInfo.value
            .shipping_method,

        payment_method:
          guestInfo.value
            .payment_method,
      }
    }

  const resetAfterOrder =
    () => {

      clearCart()

      guestInfo.value = {
        customer_name:
          '',
        phone: '',
        address: '',
        city: '',
        province:
          '',
        postal_code:
          '',
        notes: '',
        shipping_method:
          'standard',
        payment_method:
          'cod',
      }
    }

  return {
    // state
    items,
    guestInfo,
    discountAmount,

    // computed
    cartTotal,
    cartCount,
    shippingCost,
    grandTotal,

    // cart
    addItem,
    addToCart,
    removeItem,
    removeFromCart,
    updateQuantity,
    clearCart,

    // guest
    setGuestInfo,
    updateGuestInfo,
    validateGuestInfo,

    // discount
    setDiscount,

    // storage
    saveCart,
    loadCart,

    // order
    getOrderData,
    resetAfterOrder,
  }
})