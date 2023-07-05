class CallingAPI {
    api_host;
    api_token;
    api_uri;
    payload;
    api_response_code;
    api_response_data;

    constructor(api_host, api_token, api_uri, payload) {
        this.api_host = api_host;
        this.api_token = api_token;
        this.api_uri = api_uri;
        this.payload = payload;
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
            this.api_response_data = new Map(Object.entries(await response.json())); //extract JSON from the http response
        } catch (error) {
            // And of course, make sure you catch and log any errors!
            console.log(`Error while requesting ${this.api_host}/${this.api_uri}`);
            throw new Error(`Error while requesting ${this.api_host}/${this.api_uri}`);
        }
    };
};

exports.CallingAPI = CallingAPI;