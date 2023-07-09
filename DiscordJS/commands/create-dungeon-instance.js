const { SlashCommandBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('create-dungeon-instance')
		.setDescription("Préparez un nouveau groupe d'exploration")
        .addStringOption(option =>
            option.setName('donjon')
                .setDescription("Donjon à explorer")
                .setRequired(true)
                .addChoices(
                    { name: 'Égouts de Fort-Écorce', value: 'Égouts de Fort-Écorce' },
                )
        ),
	async execute(interaction) {
		var api_data = new Object()
		api_data.discordUserId = interaction.user.id;
        api_data.dungeonName = interaction.options.getString('donjon');

		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/dungeon/instance/create",
			api_data,
            interaction
		)

        await api_call.connectToAPI();

        if (api_call.getAPIResponseCode() === 200) {
            interaction.reply({
                content : `Vous avez bien créé votre groupe d'exploration !`,
                ephemeral: true
            });
        }
	},
};