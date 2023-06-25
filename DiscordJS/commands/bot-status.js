const { SlashCommandBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('bot-status')
		.setDescription('Get status information about the bot !'),
	async execute(interaction) {
		var api_data = new Object()
		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/ping",
			api_data
		)

		try {
			await api_call.connectToAPI();

			if (api_call.getAPIResponseCode() !== 200) {
				interaction.reply('Status : Not Ok :x:');
			}
			else {
				await interaction.reply(`Status : OK :white_check_mark:`);
			}
		} catch (error) {
			interaction.reply('Status : Not Ok :x:');
		}
	},
};