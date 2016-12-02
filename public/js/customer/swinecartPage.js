'use strict';

// This is the event hub we'll use in every
// component to communicate between them.
var eventHub = new Vue();

// Custom Local component
Vue.component('star-rating',{
    template: '#star-rating-template',
    props: ['type'],
    data: function(){
        return {
            starValues:[
                {
                    class: '',
                    icon: '',
                },
                {
                    class: '',
                    icon: '',
                },
                {
                    class: '',
                    icon: '',
                },
                {
                    class: '',
                    icon: '',
                },
                {
                    class: '',
                    icon: '',
                },
            ],
            normalClass: 'grey-text text-darken-2',
            toggledClass: 'yellow-text text-darken-1',
            normalIcon: 'star_border',
            toggledIcon: 'star',
            clicked: 0,
        };
    },
    methods: {
        animateHover: function(order){
            if(!this.clicked){
                // Designate appropriate classes to the stars
                this.toggleClasses(order);
                this.normalizeClasses(order);
            }
        },

        deanimateHover: function(){
            if(!this.clicked) this.normalizeClasses(-1);
        },

        getValue: function(value){
            this.clicked = 1;

            // Designate appropriate classes to the stars
            this.toggleClasses(value);
            this.normalizeClasses(value);

            // Notify Order Details component of rating value set
            switch (this.type) {
                case 'delivery':
                    this.$emit('set-delivery-rating',value+1);
                    break;
                case 'transaction':
                    this.$emit('set-transaction-rating',value+1);
                    break;
                case 'productQuality':
                    this.$emit('set-product-rating',value+1);
                    break;
                default: break;
            }
        },

        normalizeClasses: function(order){
            for (var i = order+1; i < 5; i++) {
                this.starValues[i].class = this.normalClass;
                this.starValues[i].icon = this.normalIcon;
            }
        },

        toggleClasses: function(order){
            for (var i = 0; i <= order; i++) {
                this.starValues[i].class = this.toggledClass;
                this.starValues[i].icon = this.toggledIcon;
            }
        }
    },
    created: function(){
        // Normalize classes of stars
        this.normalizeClasses(-1);

    }
});

Vue.component('quantity-input',{
    template: '\
        <div class="col s12" style="padding:0;"> \
            <input type="text" \
                ref="input" \
                class="center-align" \
                :value="value" \
                @input="updateValue($event.target.value)" \
                @focus="selectAll" \
                @blur="formatValue" \
            > \
        </div> \
    ',
    props: {
        value: {
            type: Number
        }
    },
    methods: {
        updateValue: function(value){
            var resultValue = this.validateQuantity(value);
            this.$refs.input.value = resultValue;
            this.$emit('input', resultValue);
        },

        selectAll: function(event){
            setTimeout(function () {
                event.target.select()
            }, 0);
        },

        formatValue: function(){
            this.$refs.input.value = this.validateQuantity(this.value);
        },

        validateQuantity: function(value){
            var parsedValue = _.toNumber(value);
            if(_.isFinite(parsedValue) && parsedValue > 0){
                // If it is a number check if it is divisible by 2
                if(parsedValue % 2 !== 0) return parsedValue + 1;
                else return parsedValue;
            }
            else return 2;
        }
    }

});

Vue.component('custom-date-select', {
    template: '\
        <div> \
            <input type="date" id="date-needed" name="date-needed" class="datepicker" ref="select" :value="value"/> \
            <label for="date-needed">Date Needed</label> \
        </div> \
    ',
    props:['value'],
    mounted: function(){
        $('.datepicker').pickadate({
            min: true,
            selectMonths: true,
            selectYears: 2,
            format: 'mmmm d, yyyy'
        });

        var self = this;
        $('#date-needed').on('change', function(){
            self.$emit('date-select',self.$refs.select.value);
        });
    }

});

// Custom global component
Vue.component('order-details',{
    template: '#order-details-template',
    props: ['products', 'token'],
    components: {
        // 'star-rating': StarRating
    },
    data: function(){
        return {
            productRemove:{
                index: 0,
                name: '',
                id: 0
            },
            productRequest:{
                index: 0,
                name: '',
                id: 0,
                type: '',
                dateNeeded: '',
                specialRequest: ''
            },
            requestDetails:{
                name: '',
                type: '',
                dateNeeded: '',
                specialRequest: ''
            },
            breederRate:{
                index: 0,
                breederName: '',
                deliveryValue: 0,
                transactionValue: 0,
                productQualityValue: 0,
                commentField: ''
            }
        };
    },
    filters: {
        capitalize: function(value){
            // Capitalize first letter of word
            return value[0].toUpperCase() + value.slice(1);
        }
    },
    methods: {
        subtractQuantity: function(index){
            // Update (subtract) product's quantity on root data
            this.$emit('subtract-quantity', index);
        },

        addQuantity: function(index){
            // Update (add) product's quantity on root data
            this.$emit('add-quantity', index);
        },

        dateChange: function(value){
            this.productRequest.dateNeeded = value;
        },

        viewProductModalFromCart: function(index){
            vm.productInfoModal.imgPath = this.products[index].img_path;
            vm.productInfoModal.name = this.products[index].product_name;
            vm.productInfoModal.breeder = this.products[index].breeder;
            vm.productInfoModal.province = this.products[index].product_province;
            vm.productInfoModal.type = this.products[index].product_type;
            vm.productInfoModal.breed = this.products[index].product_breed;
            vm.productInfoModal.birthdate = this.products[index].product_birthdate;
            vm.productInfoModal.age = this.products[index].product_age;
            vm.productInfoModal.adg = this.products[index].product_adg;
            vm.productInfoModal.fcr = this.products[index].product_fcr;
            vm.productInfoModal.bft = this.products[index].product_backfat_thickness;
            vm.productInfoModal.avgDelivery = this.products[index].avg_delivery;
            vm.productInfoModal.avgTransaction = this.products[index].avg_transaction;
            vm.productInfoModal.avgProductQuality = this.products[index].avg_productQuality;
            vm.productInfoModal.otherDetails = this.products[index].other_details.split(',');

            $('#info-modal').openModal({
                opacity: 0
            });
        },

        viewRequestDetails: function(index){
            this.requestDetails.name = this.products[index].product_name;
            this.requestDetails.type = this.products[index].product_type;
            this.requestDetails.dateNeeded = this.products[index].date_needed;
            this.requestDetails.specialRequest = this.products[index].special_request;

            $('#product-request-details-modal').openModal({
                opacity: 0
            });
        },

        confirmRemoval: function(index){
            this.productRemove.index = index;
            this.productRemove.name = this.products[index].product_name;
            this.productRemove.id = this.products[index].product_id;
            $('#remove-product-confirmation-modal').openModal();
        },

        removeProduct: function(){
            var index = this.productRemove.index;

            $('#remove-product-confirmation-modal').closeModal();

            // Do AJAX
            this.$http.delete(
                config.swineCart_url + '/delete',
                {
                    body:{
                        _token: this.token,
                        itemId: this.products[index].item_id
                    }
                }
            ).then(
                function(response){

                    var data = response.body;

                    // If deletion of item is successful
                    if (data[0] === 'success') {
                        var span = $('#cart-icon span');

                        // Put quantity of Swine Cart to sessionStorage
                        sessionStorage.setItem('swine_cart_quantity', data[2]);

                        if (data[2] === 0) {
                            span.html("");
                            span.removeClass('badge');
                            $('#cart-icon .material-icons').removeClass('left');
                            $('#cart-dropdown #item-container').html(
                                '<li> <span class="center-align black-text"> No items in your Swine Cart </span> </li>'
                            );
                        } else span.html(sessionStorage.getItem('swine_cart_quantity'));

                        // Update (remove product) list of products on root data
                        this.$emit('remove-product', index);

                        Materialize.toast(data[1] + ' removed from Swine Cart', 1800, 'green lighten-1');

                    } else Materialize.toast(data[1] + ' is ' + data[0], 1500, 'orange accent-2');

                },
                function(response){
                    console.log(response.statusText);
                }
            );

        },

        confirmRequest: function(index){
            this.productRequest.index = index;
            this.productRequest.name = this.products[index].product_name;
            this.productRequest.id = this.products[index].product_id;
            this.productRequest.type = this.products[index].product_type;
            this.productRequest.dateNeeded = this.products[index].date_needed;
            this.productRequest.specialRequest = this.products[index].special_request;
            $('#request-product-confirmation-modal').openModal();
        },

        requestProduct: function(){
            var index = this.productRequest.index;

            $('#request-product-confirmation-modal').closeModal();

            // Do AJAX
            this.$http.patch(
                config.swineCart_url + '/request',
                {
                    _token: this.token,
                    itemId: this.products[index].item_id,
                    productId: this.products[index].product_id,
                    requestQuantity: this.products[index].request_quantity,
                    dateNeeded: this.productRequest.dateNeeded,
                    specialRequest: this.productRequest.specialRequest
                }
            ).then(
                function(response){
                    var data = response.body;
                    var span = $('#cart-icon span');

                    // Update necessary product attributes on root data
                    var updateDetails = {
                        index: index,
                        status: 'requested',
                        requestStatus: 1,
                        dateNeeded: this.productRequest.dateNeeded,
                        specialRequest: this.productRequest.specialRequest,
                        statusTransaction: data[1]
                    };

                    this.$emit('product-requested', updateDetails);

                    // Put quantity of Swine Cart to sessionStorage
                    sessionStorage.setItem('swine_cart_quantity', data[0]);

                    if(data[0] == 0){
                        span.html("");
                        span.removeClass('badge');
                        $('#cart-icon .material-icons').removeClass('left');
                        $('#cart-dropdown #item-container').html(
                            '<li> <span class="center-align black-text"> No items in your Swine Cart </span> </li>'
                        );
                    }
                    else span.html(sessionStorage.getItem('swine_cart_quantity'));

                    Materialize.toast(this.products[index].product_name + ' requested', 1800, 'green lighten-1')

                    // Update some DOM elements
                    this.$nextTick(function(){
                        $('.tooltipped').tooltip({delay:50});
                        $('label[for="special-request"]').removeClass('active');
                        $('label[for="date-needed"]').removeClass('active');
                    });

                },
                function(response){
                    console.log(response.statusText);
                }
            );

        },

        showRateModal: function(index){
            $('#rate-modal').openModal();

            this.breederRate.index = index;
            this.breederRate.breederName = this.products[index].breeder;
        },

        rateAndRecord: function(){
            var index = this.breederRate.index;

            $('#rate-modal').closeModal();

            // Do AJAX
            this.$http.post(
                config.swineCart_url + '/rate',
                {
                    _token: this.token,
                    breederId: this.products[index].breeder_id,
                    customerId: this.products[index].customer_id,
                    productId: this.products[index].product_id,
                    delivery: this.breederRate.deliveryValue,
                    transaction: this.breederRate.transactionValue,
                    productQuality: this.breederRate.productQualityValue,
                    comment: this.breederRate.commentField
                }
            ).then(
                function(response){
                    Materialize.toast(this.products[index].breeder + ' rated', 1800, 'green lighten-1');

                    // Update local storage of the products
                    this.breederRate.commentField = '';
                    this.breederRate.deliveryValue = 0;
                    this.breederRate.transactionValue = 0;
                    this.breederRate.productQualityValue = 0;

                    // Put the rated product to transaction-history component
                    this.$emit('update-history',
                        { 'index': index }
                    );
                },
                function(response){
                    console.log(response.statusText);
                }
            );

            // Normalize classes of rating modal
            this.$refs.delivery.normalizeClasses(-1);
            this.$refs.transaction.normalizeClasses(-1);
            this.$refs.productQuality.normalizeClasses(-1);
        },

        setDeliveryRating: function(value){
            // Listener to 'set-delivery-rating' from 'star-rating' component
            this.breederRate.deliveryValue = value;
        },

        setTransactionRating: function(value){
            // Listener to 'set-transaction-rating' from 'star-rating' component
            this.breederRate.transactionValue = value;
        },

        setProductRating: function(value){
            // Listener to 'set-product-rating' from 'star-rating' component
            this.breederRate.productQualityValue = value;
        }
    }
});

Vue.component('transaction-history',{
    template: '#transaction-history-template',
    props: ['history'],
    data: function(){
        return {

        };
    },
    methods: {
        viewProductModalFromHistory: function(index){
            vm.productInfoModal.imgPath = this.history[index].img_path;
            vm.productInfoModal.name = this.history[index].product_name;
            vm.productInfoModal.breeder = this.history[index].breeder;
            vm.productInfoModal.type = this.history[index].product_type;
            vm.productInfoModal.breed = this.history[index].product_breed;
            vm.productInfoModal.age = this.history[index].product_age;
            vm.productInfoModal.adg = this.history[index].product_adg;
            vm.productInfoModal.fcr = this.history[index].product_fcr;
            vm.productInfoModal.bft = this.history[index].product_backfat_thickness;
            vm.productInfoModal.avgDelivery = this.history[index].avg_delivery;
            vm.productInfoModal.avgTransaction = this.history[index].avg_transaction;
            vm.productInfoModal.avgProductQuality = this.history[index].avg_productQuality;
            vm.productInfoModal.otherDetails = this.history[index].other_details;

            $('#info-modal').openModal({
                opacity: 0
            });
        }

    },
    filters: {
        capitalize: function(value){
            // Capitalize first letter of word
            if(value){
                var str = value;
                return str[0].toUpperCase() + str.slice(1);
            }
            return '';
        }
    }
});

// Root component
var vm = new Vue({
    el:'#swine-cart-container',
    data: {
        products: rawProducts,
        history: [],
        productInfoModal:{
            imgPath: '',
            name: '',
            breeder: '',
            farm_province: '',
            type: '',
            breed: '',
            birthdate: '',
            age: 0,
            adg: 0,
            fcr: 0,
            bft: 0,
            avgDelivery: 0,
            avgTransaction: 0,
            avgProductQuality: 0,
            otherDetails: []
        }
    },
    computed: {
        capitalizedProductType: function(){
            // Capitalize first letter of the word
            if(this.productInfoModal.type){
                var str = this.productInfoModal.type;
                return str[0].toUpperCase() + str.slice(1);
            }
            return '';
        }
    },
    methods: {
        subtractProductQuantity: function(index){
            // Listener to 'subtract-quantity' from order-details component
            if(this.products[index].request_quantity > 2) this.products[index].request_quantity -= 2;
        },

        addProductQuantity: function(index){
            // Listener to 'add-quantity' from order-details component
            this.products[index].request_quantity += 2 ;
        },

        updateHistory: function(updateDetails){
            // Listener to 'update-history' from order-details component
            this.products.splice(updateDetails.index,1);
        },

        removeProduct: function(index){
            // Listener to 'remove-product' from order-details component
            this.products.splice(index,1);
        },

        productRequested: function(updateDetails){
            var index = updateDetails.index;
            this.products[index].status = 'requested';
            this.products[index].request_status = 1;
            this.products[index].date_needed = updateDetails.dateNeeded;
            this.products[index].special_request = updateDetails.specialRequest;
            this.products[index].status_transactions['requested'] = updateDetails.statusTransaction;
        },

        getTransactionHistory: function(customerId){

            // Do AJAX
            this.$http.get(
                config.swineCart_url+'/transaction-history',
                {
                    params: { customerId: customerId }
                }
            ).then(
                function(response){

                    // Store fetched data in local component data
                    this.history = JSON.parse(response.body);

                    // Parse JSON objects
                    for (var i = 0; i < this.history.length; i++) {
                        this.history[i].product_details = JSON.parse(this.history[i].product_details);
                        this.history[i].status_transactions = JSON.parse(this.history[i].status_transactions);
                    }
                },
                function(response){
                    console.log(response.statusText);
                }
            );

        }
    },
    filters: {
        round: function(value){
            // Round number according to precision
            var precision = 2;
            var factor = Math.pow(10, precision);
            var tempNumber = value * factor;
            var roundedTempNumber = Math.round(tempNumber);
            return roundedTempNumber / factor;
        }
    }
});
