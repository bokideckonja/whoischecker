<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Domain whois checker</title>
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
                        <div class="control is-expanded has-icons-left">
                            <input v-model="domain" class="input is-success is-large" type="text" name="domain" placeholder="e.g. example.com">
                            <span class="icon is-small is-left">
                                <i class="fas fa-globe"></i>
                            </span>
                        </div>
                        <div class="control">
                            <button type="button" v-on:click="onSubmit" v-bind:class="{ 'is-loading': checking }" class="button is-success is-large" :disabled="checking">
                            Check
                            </button>
                        </div>
                    </div>
                </form>


                <div class="content" v-if="lastDomainCheck.run">
                    <h2 class="title is-1" :class="textClass">{{ status }}</h2>
                </div>

                <div class="content" v-if="lastDomainCheck.whois">
                    <div class="box" >
                        <pre v-html="lastDomainCheck.whois">
                        
                        </pre>
                    </div>
                </div>
        </section>
    </div>
    <script src="app.js"></script>
</body>
</html>