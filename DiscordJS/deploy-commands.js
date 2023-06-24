require('dotenv').config()
const { REST, Routes } = require('discord.js');
const fs = require('node:fs');
let token;
let client_id;

switch(process.env.STATUS) {
    case "production":
        token = process.env.PROD_TOKEN;
        client_id = process.env.PROD_CLIENT_ID;
        console.log('Connecting with PROD');
        break;
    case "dev-tako":
        token = process.env.DEV_TAKO_TOKEN;
        client_id = process.env.DEV_TAKO_CLIENT_ID;
        console.log('Connecting with DEV_TAKO');
        break;
    case "dev-zaos":
        token = process.env.DEV_ZAOS_TOKEN;
        client_id = process.env.DEV_ZAOS_CLIENT_ID;
        console.log('Connecting with DEV_ZAOS');
        break;
    default:
        token = process.env.DEV_TOKEN;
        client_id = process.env.DEV_CLIENT_ID;
        console.log('Connecting with DEV/DEFAULT');
}

const commands = [];
// Grab all the command files from the commands directory you created earlier
const commandFiles = fs.readdirSync('./commands').filter(file => file.endsWith('.js'));

// Construct and prepare an instance of the REST module
const rest = new REST({ version: '10' }).setToken(token);

// and deploy your commands!
(async () => {
	try {
        // Grab the SlashCommandBuilder#toJSON() output of each command's data for deployment
        for (const file of commandFiles) {
            const command = require(`./commands/${file}`);
            let commandData = await command.data;
            
            commands.push(commandData.toJSON());
        }

		console.log(`Started refreshing ${commands.length} application (/) commands.`);

		// The put method is used to fully refresh all commands in the guild with the current set
		const data = await rest.put(
			Routes.applicationCommands(client_id),
			{ body: commands },
		);

		console.log(`Successfully reloaded ${data.length} application (/) commands.`);
	} catch (error) {
		// And of course, make sure you catch and log any errors!
		console.error('error');
	}
})();