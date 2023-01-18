const { SlashCommandBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('ping-api')
		.setDescription('Test connectivity with Backend API'),
	async execute(interaction) {
		var api_data = new Object()
		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/ping",
			JSON.stringify(api_data)
		)
		await api_call.connectToAPI()
		await interaction.reply(`Code ${api_call.getAPIResponseCode()} : API said ${api_call.getAPIResponseData().get("message")}`);
	},
};