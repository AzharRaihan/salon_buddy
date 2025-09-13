<template>
    <div class="loyalty-points-display" v-if="showLoyaltyPoints">
        <div class="loyalty-points-card">
            <div class="loyalty-points-header">
                <VIcon icon="tabler-star" class="loyalty-icon" />
                <span class="loyalty-title">{{ t('Loyalty Points') }}</span>
            </div>
            
            <div class="loyalty-points-content">
                <div class="loyalty-points-row">
                    <span class="label">{{ t('Current Points') }}:</span>
                    <span class="value">{{ customerLoyaltyPoints }}</span>
                </div>
                
                <div class="loyalty-points-row" v-if="loyaltyPointsNeeded > 0">
                    <span class="label">{{ t('Points Needed') }}:</span>
                    <span class="value">{{ loyaltyPointsNeeded }}</span>
                </div>
                
                <div class="loyalty-points-row" v-if="loyaltyPointsValue > 0">
                    <span class="label">{{ t('Points Value') }}:</span>
                    <span class="value">{{ formatCurrency(loyaltyPointsValue) }}</span>
                </div>
                
                <div class="loyalty-points-status" v-if="hasEnoughLoyaltyPoints">
                    <VIcon icon="tabler-check-circle" class="status-icon success" />
                    <span class="status-text success">{{ t('Enough points available') }}</span>
                </div>
                
                <div class="loyalty-points-status" v-else-if="loyaltyPointsNeeded > 0">
                    <VIcon icon="tabler-alert-circle" class="status-icon warning" />
                    <span class="status-text warning">{{ t('Insufficient points') }}</span>
                </div>
                
                <div class="loyalty-points-info" v-if="companyLoyaltySettings.minimum_point_to_redeem > 0">
                    <small class="info-text">
                        {{ t('Minimum points to redeem') }}: {{ companyLoyaltySettings.minimum_point_to_redeem }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    customerLoyaltyPoints: {
        type: Number,
        default: 0
    },
    loyaltyPointsNeeded: {
        type: Number,
        default: 0
    },
    loyaltyPointsValue: {
        type: Number,
        default: 0
    },
    hasEnoughLoyaltyPoints: {
        type: Boolean,
        default: false
    },
    companyLoyaltySettings: {
        type: Object,
        default: () => ({
            minimum_point_to_redeem: 0,
            loyalty_rate: 0
        })
    },
    selectedCustomer: {
        type: Object,
        default: null
    }
})

const showLoyaltyPoints = computed(() => {
    return props.selectedCustomer && 
           props.selectedCustomer.name !== 'Walk-in Customer' && 
           props.selectedCustomer.name !== 'Walking-in Customer' &&
           props.companyLoyaltySettings.loyalty_rate > 0
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount)
}
</script>

<style scoped>
.loyalty-points-display {
    margin-bottom: 1rem;
}

.loyalty-points-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1rem;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.loyalty-points-header {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    font-weight: 600;
    font-size: 1.1rem;
}

.loyalty-icon {
    margin-right: 0.5rem;
    font-size: 1.2rem;
}

.loyalty-title {
    font-weight: 600;
}

.loyalty-points-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.loyalty-points-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
}

.label {
    font-weight: 500;
    opacity: 0.9;
}

.value {
    font-weight: 600;
    font-size: 1.1rem;
}

.loyalty-points-status {
    display: flex;
    align-items: center;
    margin-top: 0.5rem;
    padding: 0.5rem;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
}

.status-icon {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.status-icon.success {
    color: #10b981;
}

.status-icon.warning {
    color: #f59e0b;
}

.status-text {
    font-weight: 500;
    font-size: 0.9rem;
}

.status-text.success {
    color: #10b981;
}

.status-text.warning {
    color: #f59e0b;
}

.loyalty-points-info {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.info-text {
    opacity: 0.8;
    font-size: 0.85rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .loyalty-points-card {
        padding: 0.75rem;
    }
    
    .loyalty-points-header {
        font-size: 1rem;
    }
    
    .value {
        font-size: 1rem;
    }
}
</style>

