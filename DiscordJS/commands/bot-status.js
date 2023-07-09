const { SlashCommandBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('bot-status')
		.setDescription("Vérifiez l'état actuel du bot."),
	async execute(interaction) {
		var api_data = new Object()
		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/ping",
			api_data
		)

		await api_call.connectToAPI();

		if (api_call.getAPIResponseCode() === 200) {
			await interaction.reply({
				content: `Status : En ligne :white_check_mark:`,
				ephemeral: true
			});
		}
		else {
			await interaction.reply({
				content: 'Status : Hors ligne :x:',
				ephemeral: true
			});
		}
	},
};