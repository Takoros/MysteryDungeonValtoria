const { SlashCommandBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('modify-description')
		.setDescription('Modifiez la description de votre personnage')
        .addStringOption(option =>
            option.setName('description')
                .setDescription("Votre nouvelle description")
                .setRequired(true)
				.setMaxLength(100)
        ),
	async execute(interaction) {
		var api_data = new Object()
		api_data.discordUserId = interaction.user.id;
		api_data.description = interaction.options.getString('description');

		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/character/modify/description",
			api_data,
			interaction
		)

		await api_call.connectToAPI();

		if (api_call.getAPIResponseCode() === 200) {
			await interaction.reply(`Description modifiée avec succès !`);
		}
	},
};