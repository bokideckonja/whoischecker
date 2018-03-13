// Instantiate new Vue
var app = new Vue({
    el: '#app',
    data: {
        // Model bind to input
        domain: '',
        // Quering in progress
        checking: false,
        // Last domain check results
        lastDomainCheck: {
            run: false,
            success: false,
            domain: '',
            available: '',
            whois: false,
            message: '',
        },
    },
    computed: {
        // Compute status text
        status(){
            if(this.lastDomainCheck.success){
                return this.lastDomainCheck.available ? this.lastDomainCheck.domain + " is available" : this.lastDomainCheck.domain + " is taken" ;
            }else{
                return this.lastDomainCheck.message;
            }
        },
        // Compute status text class
        textClass(){
            if(this.lastDomainCheck.success){
                return this.lastDomainCheck.available ? "has-text-primary" : "has-text-danger";
            }else{
                return "has-text-danger";
            }
        }
    },
    methods: {
        // On form submit action
        onSubmit(){
            this.checking = true;
            var _this = this;
            // Perform ajax request
            axios.get('/rest.php', {
                params: {
                    domain: this.domain
                }
            })
            .then(function (response) {
                if(response.data.success == true){
                    _this.lastDomainCheck.available = response.data.available;
                    _this.lastDomainCheck.whois = response.data.whois;
                }else{
                    _this.lastDomainCheck.whois = false;
                    _this.lastDomainCheck.message = response.data.message;
                }
                _this.lastDomainCheck.success = response.data.success;
                _this.lastDomainCheck.domain = response.data.domain;
            })
            .catch(function (error) {
                // On server error, manualy set params
                _this.lastDomainCheck.success = false;
                _this.lastDomainCheck.whois = false;
                _this.lastDomainCheck.message = "Error checking domain.";
            })
            .then(function(){
                // Second then is allways executed
                _this.lastDomainCheck.run = true;
                _this.checking = false;
            });
        }
    }
})