class CallingAPI {
    api_host;
    api_token;
    api_uri;
    payload;
    interaction;
    api_response_code;
    api_response_data;

    constructor(api_host, api_token, api_uri, payload, interaction = null) {
        this.api_host = api_host;
        this.api_token = api_token;
        this.api_uri = api_uri;
        this.payload = payload;
        this.interaction = interaction;
    }

    setHost(api_host) {
        this.api_host = api_host;

        return this;
    }

    setURI(api_uri) {
        this.api_uri = api_uri;

        return this;
    }

    setAPIToken(api_token) {
        this.api_token = api_token;

        return this;
    }

    setPayload(payload) {
        this.payload = payload;

        return this;
    }

    getAPIResponseCode() {
        return this.api_response_code;
    };

    getAPIResponseData() {
        return this.api_response_data;
    };

    async connectToAPI() {
        try {
            const response = await fetch(`${this.api_host}/${this.api_uri}`, {
                method: 'POST',
                body: JSON.stringify({
                    "token": this.api_token, 
                    "data": this.payload
                }), // string or object
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            this.api_response_code = response.status
            this.api_response_data = new Map(Object.entries(await response.json()));
            this.sendErrorMessage();
        } catch (error) {
            this.api_response_code = 500;
            this.api_response_data = null;
            this.sendErrorMessage();

            console.log(`Error while requesting ${this.api_host}/${this.api_uri}`);
        }
    };

    sendErrorMessage(){
        if(!this.interaction || this.getAPIResponseCode() === 200){
            return;
        }

        if(this.getAPIResponseCode() === 500 || this.getAPIResponseData().get('message') === undefined || this.getAPIResponseData().get('message') === ''){
            this.interaction.reply({
                content: 'Une erreur est survenue, veuillez contacter un administrateur ou réessayer plus tard.',
                ephemeral: true
            });
        }
        else {
            this.interaction.reply({
                content: this.getAPIResponseData().get('message'),
                ephemeral: true
            });
        }
    }
};

exports.CallingAPI = CallingAPI;