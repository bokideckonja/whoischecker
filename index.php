<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hello Bulma!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.6.2/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
</head>
<body>
    <div id="app" >
        <section class="section">
            <div class="container">
                <h1 class="title">Domain checker</h1>
                <p class="subtitle">Check if your domain is available for purchase!</p>

                <form v-on:submit.prevent="onSubmit" action="rest.php" method="GET" autocomplete="off" class="content">
                    <div class="field has-addons">
                        <div class="control is-expanded has-icons-left has-icons-right">
                            <input v-model="domain" class="input is-success is-large" type="text" name="domain" placeholder="e.g. example.com">
                            <span class="icon is-small is-left">
                                <i class="fas fa-globe"></i>
                            </span>
                            <span class="icon is-small is-right">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                        <div class="control">
                            <button type="button" v-on:click="onSubmit" v-bind:class="{ 'is-loading': isChecking }" class="button is-success is-large" :disabled="isChecking">
                            Check
                            </button>
                        </div>
                    </div>
                </form>


                <div class="content" v-if="hasSubmited">
                    <h3 class="title has-text-primary is-spaced">{{ status }}</h3>
                </div>

                <div class="content" v-if="whois">
                    <div class="box" >
                        <pre v-html="whois">
                        
                        </pre>
                    </div>
                </div>
        </section>
    </div>
    <script>
        var app = new Vue({
            el: '#app',
            data: {
                domain: '',
                lastDomain: {
                    name: '',
                    available: '',
                    status: ''
                },
                checking: false,
                hasSubmited: false,
                whois: false,
            },
            computed: {
                isChecking(){
                    return this.checking;
                },
                status(){
                    return this.lastDomain.available ? this.lastDomain.name + ' is available.' : this.lastDomain.name + ' is taken';
                }
            },
            methods: {
                onSubmit(){
                    this.lastDomain.name = this.domain;
                    this.hasSubmited = true;
                    this.checking = true;
                    var _this = this;
                    axios.get('/rest.php', {
                        params: {
                            domain: this.domain
                        }
                    })
                    .then(function (response) {
                        
                        _this.checking = false;
                        _this.whois = response.data.whois;
                        _this.lastDomain.available = response.data.available;
                        console.log(response);
                    })
                    .catch(function (error) {
                        _this.checking = false;
                        _this.whois = false;
                    });
                }
            }
        })
    </script>
</body>
</html>