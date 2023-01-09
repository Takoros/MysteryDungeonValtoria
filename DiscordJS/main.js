require('dotenv').config()
const { Client, GatewayIntentBits, REST, Routes } = require('discord.js');
const client = new Client({ intents: [GatewayIntentBits.Guilds] });
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

const commands = [
  {
    name: 'ping',
    description: 'Replies with Pong!',
  },
];

const rest = new REST({ version: '10' }).setToken(token);

(async () => {
  try {
    console.log('Started refreshing application (/) commands.');

    await rest.put(Routes.applicationCommands(client_id), { body: commands });

    console.log('Successfully reloaded application (/) commands.');
  } catch (error) {
    console.error(error);
  }
})();

client.on('ready', () => {
  console.log(`Logged in as ${client.user.tag}!`);
});

client.on('interactionCreate', async interaction => {
  if (!interaction.isChatInputCommand()) return;

  if (interaction.commandName === 'ping') {
    await interaction.reply('Pong!');
  }
});

client.login(token);