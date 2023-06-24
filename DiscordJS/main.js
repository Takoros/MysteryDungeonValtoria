require('dotenv').config()
const fs = require('node:fs');
const path = require('node:path');
const { Client, Collection, GatewayIntentBits, REST, Routes } = require('discord.js');
const client = new Client({ intents: [GatewayIntentBits.Guilds] });

// Loading env vars
client.env = new Collection();
switch(process.env.STATUS) {
    case "production":
        client.env.set("token", process.env.PROD_TOKEN);
        client.env.set("client_id", process.env.PROD_CLIENT_ID);
        client.env.set("api_host", process.env.PROD_API_HOST);
        client.env.set("api_token", process.env.DISCORDBOT_TOKEN);
        console.log('Connecting with PROD');
        break;
    case "dev-tako":
        client.env.set("token", process.env.DEV_TAKO_TOKEN);
        client.env.set("client_id", process.env.DEV_TAKO_CLIENT_ID);
        client.env.set("api_host", process.env.DEV_API_HOST);
        client.env.set("api_token", process.env.DISCORDBOT_TOKEN);
        console.log('Connecting with DEV_TAKO');
        break;
    case "dev-zaos":
        client.env.set("token", process.env.DEV_ZAOS_TOKEN);
        client.env.set("client_id", process.env.DEV_ZAOS_CLIENT_ID);
        client.env.set("api_host", process.env.DEV_API_HOST);
        client.env.set("api_token", process.env.DISCORDBOT_TOKEN);
        console.log('Connecting with DEV_ZAOS');
        break;
    default:
        client.env.set("token", process.env.DEV_TOKEN);
        client.env.set("client_id", process.env.DEV_CLIENT_ID);
        client.env.set("api_host", process.env.DEV_API_HOST);
        client.env.set("api_token", process.env.DISCORDBOT_TOKEN);
        console.log('Connecting with DEV/DEFAULT');
}

(async () => {
	try {
        // Loading commands
        client.commands = new Collection();
        const commandsPath = path.join(__dirname, 'commands');
        const commandFiles = fs.readdirSync(commandsPath).filter(file => file.endsWith('.js'));

        for (const file of commandFiles) {
            const filePath = path.join(commandsPath, file);
            const command = require(filePath)

            if(command.data instanceof Promise){
                const commandData = await command.data;

                if (commandData && 'execute' in command) {
                    client.commands.set(commandData.name, command);
                } else {
                    console.log(`[WARNING] The command at ${filePath} is missing a required "data" or "execute" property.`);
                }
            }
            else {
                // Set a new item in the Collection with the key as the command name and the value as the exported module
                if ('data' in command && 'execute' in command) {
                    client.commands.set(command.data.name, command);
                } else {
                    console.log(`[WARNING] The command at ${filePath} is missing a required "data" or "execute" property.`);
                }
            }
        }

        // Loading events
        const eventsPath = path.join(__dirname, 'events');
        const eventFiles = fs.readdirSync(eventsPath).filter(file => file.endsWith('.js'));

        for (const file of eventFiles) {
            const filePath = path.join(eventsPath, file);
            const event = require(filePath);
            if (event.once) {
                client.once(event.name, (...args) => event.execute(...args));
            } else {
                client.on(event.name, (...args) => event.execute(...args));
            }
        }

        client.login(client.env.get("token")); 
	} catch (error) {
        console.log(error);
	}
})();