<template>
  <v-container>
    <v-row>
      <v-col>
        <p class="white-page-title-label">
          {{ $t('finance.title') }}
        </p>
      </v-col>
    </v-row>
    <div class="finance-row">
      <span class="card-label">
        {{ cardBrand ? cardBrand + ' ' + cardLastFour : $t('finance.no_payment_method') }}
      </span>
      <v-btn class="payment-method-button" to="/finances/stripe">
        {{ $t(cardBrand ? 'finance.update_payment_method' : 'finance.add_payment_method') }}
      </v-btn>
    </div>

    <div class="finance-row">
      <span class="row-title-label">{{ $t('finance.balance') }}</span>
      <span class="row-value-label balance-value-label">{{ balance }}</span>
    </div>

    <v-row align="center" justify="space-around" class="mt-3 mb-3">
            <v-btn class="app-red-button" @click="payBalance" v-if="showPayButton()">
        {{ $t('common.pay') }}
      </v-btn>
    </v-row>

    <div id="invoice-view">
      <span class="invoice-title-label mb-2">{{ $t('finance.invoices') }}</span>
      <table class="q-key-table">
        <thead>
          <tr>
            <th>{{ $t('table_fields.date') }}</th>
            <th>{{ $t('table_fields.amount') }}</th>
            <th>{{ $t('table_fields.status') }}</th>
          </tr>
        </thead>
        <tbody v-for="invoice of invoices" :key="invoice.id">
          <tr>
            <td>{{ invoice.updated_at | moment("DD.MM.YYYY") }}</td>
            <td>{{ invoice.amount | currency(currentCurrency, 2, {spaceBetweenAmountAndSymbol: true}) }}</td>
            <td>{{ invoice.status }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </v-container>
</template>
<script>
import axios from 'axios'

export default {

  data: () => ({
    balance: null,
    cardLastFour: null,
    cardBrand: null,
    invoices: []
  }),

  computed: {
    currentCurrency () {
      return process.env.MIX_CURRENCY
    }
  },

  metaInfo () {
    return { title: this.$t('finance.title') }
  },

  mounted () {
    this.getFinanceInfo()
  },

  methods: {
    getFinanceInfo () {
      this.$showLoading()
      axios.get('/finances').then((res) => {
        const data = res.data.data
        this.balance = data.balance
        this.cardLastFour = data.card_last_four
        this.cardBrand = data.card_brand
        this.invoices = data.invoices.data
      }).catch((error) => {
        this.$processApiErrorMessage(error)
      })
    },
    payBalance () {
      this.$showLoading();
      axios.post('/finances/pay')
        .then((res) => {
          this.$showLoading();
          this.getFinanceInfo();
        }).catch((error) => {
        this.$processApiErrorMessage(error)
      })
    },
    showPayButton(){
        if(this.balance === 0 || this.cardLastFour === null){
            return false;
        }
        return true;
    }
  }

}
</script>
<style lang="scss">
@import "./main";
</style>
