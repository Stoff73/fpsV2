<template>
  <div v-if="show" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title">{{ isEdit ? 'Edit Property' : 'Add Property' }}</h3>
        <button @click="closeModal" class="close-button">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="submitForm" class="modal-body">
        <!-- Property Type -->
        <div class="form-group">
          <label for="property_type" class="form-label">Property Type *</label>
          <select
            id="property_type"
            v-model="formData.property_type"
            class="form-select"
            required
          >
            <option value="">Select property type</option>
            <option value="main_residence">Main Residence</option>
            <option value="secondary_residence">Secondary Residence</option>
            <option value="buy_to_let">Buy to Let</option>
            <option value="commercial">Commercial Property</option>
            <option value="land">Land</option>
          </select>
          <span v-if="errors.property_type" class="error-message">{{ errors.property_type }}</span>
        </div>

        <!-- Address -->
        <div class="form-group">
          <label for="address" class="form-label">Address *</label>
          <input
            id="address"
            v-model="formData.address"
            type="text"
            class="form-input"
            placeholder="123 Main Street, London"
            required
          />
          <span v-if="errors.address" class="error-message">{{ errors.address }}</span>
        </div>

        <!-- Postcode -->
        <div class="form-group">
          <label for="postcode" class="form-label">Postcode</label>
          <input
            id="postcode"
            v-model="formData.postcode"
            type="text"
            class="form-input"
            placeholder="SW1A 1AA"
          />
        </div>

        <!-- Current Value -->
        <div class="form-group">
          <label for="current_value" class="form-label">Current Value (£) *</label>
          <input
            id="current_value"
            v-model.number="formData.current_value"
            type="number"
            step="1000"
            min="0"
            class="form-input"
            placeholder="500000"
            required
          />
          <span v-if="errors.current_value" class="error-message">{{ errors.current_value }}</span>
        </div>

        <!-- Purchase Price -->
        <div class="form-group">
          <label for="purchase_price" class="form-label">Purchase Price (£)</label>
          <input
            id="purchase_price"
            v-model.number="formData.purchase_price"
            type="number"
            step="1000"
            min="0"
            class="form-input"
            placeholder="400000"
          />
        </div>

        <!-- Purchase Date -->
        <div class="form-group">
          <label for="purchase_date" class="form-label">Purchase Date</label>
          <input
            id="purchase_date"
            v-model="formData.purchase_date"
            type="date"
            class="form-input"
          />
        </div>

        <!-- Outstanding Mortgage -->
        <div class="form-group">
          <label for="outstanding_mortgage" class="form-label">Outstanding Mortgage (£)</label>
          <input
            id="outstanding_mortgage"
            v-model.number="formData.outstanding_mortgage"
            type="number"
            step="1000"
            min="0"
            class="form-input"
            placeholder="200000"
          />
        </div>

        <!-- Rental Income (for Buy to Let) -->
        <div v-if="formData.property_type === 'buy_to_let'" class="form-group">
          <label for="rental_income" class="form-label">Monthly Rental Income (£)</label>
          <input
            id="rental_income"
            v-model.number="formData.rental_income"
            type="number"
            step="100"
            min="0"
            class="form-input"
            placeholder="2000"
          />
        </div>

        <!-- Notes -->
        <div class="form-group">
          <label for="notes" class="form-label">Notes</label>
          <textarea
            id="notes"
            v-model="formData.notes"
            class="form-textarea"
            rows="3"
            placeholder="Additional notes about this property"
          ></textarea>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
          <button type="button" @click="closeModal" class="btn-secondary">
            Cancel
          </button>
          <button type="submit" :disabled="submitting" class="btn-primary">
            {{ submitting ? 'Saving...' : (isEdit ? 'Update Property' : 'Add Property') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PropertyForm',

  props: {
    show: {
      type: Boolean,
      default: false,
    },
    property: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      formData: {
        property_type: '',
        address: '',
        postcode: '',
        current_value: null,
        purchase_price: null,
        purchase_date: null,
        outstanding_mortgage: 0,
        rental_income: null,
        notes: '',
      },
      errors: {},
      submitting: false,
    };
  },

  computed: {
    isEdit() {
      return this.property && this.property.id;
    },
  },

  watch: {
    property: {
      immediate: true,
      handler(newProperty) {
        if (newProperty) {
          this.formData = { ...newProperty };
        } else {
          this.resetForm();
        }
      },
    },
  },

  methods: {
    closeModal() {
      this.$emit('close');
      this.resetForm();
    },

    resetForm() {
      this.formData = {
        property_type: '',
        address: '',
        postcode: '',
        current_value: null,
        purchase_price: null,
        purchase_date: null,
        outstanding_mortgage: 0,
        rental_income: null,
        notes: '',
      };
      this.errors = {};
      this.submitting = false;
    },

    validateForm() {
      this.errors = {};
      let isValid = true;

      if (!this.formData.property_type) {
        this.errors.property_type = 'Property type is required';
        isValid = false;
      }

      if (!this.formData.address) {
        this.errors.address = 'Address is required';
        isValid = false;
      }

      if (!this.formData.current_value || this.formData.current_value <= 0) {
        this.errors.current_value = 'Current value must be greater than 0';
        isValid = false;
      }

      return isValid;
    },

    submitForm() {
      if (!this.validateForm()) {
        return;
      }

      this.submitting = true;
      this.$emit('save', this.formData);
    },
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 16px;
}

.modal-container {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-title {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.close-button {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: all 0.2s;
}

.close-button:hover {
  background: #f3f4f6;
  color: #111827;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  color: #111827;
  transition: all 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
  resize: vertical;
  font-family: inherit;
}

.error-message {
  display: block;
  color: #ef4444;
  font-size: 12px;
  margin-top: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.btn-primary,
.btn-secondary {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-primary:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.btn-secondary {
  background: white;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

@media (max-width: 640px) {
  .modal-container {
    max-height: 100vh;
    border-radius: 0;
  }

  .modal-header,
  .modal-body {
    padding: 16px;
  }

  .form-actions {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }
}
</style>
