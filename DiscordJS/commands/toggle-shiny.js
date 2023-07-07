const { SlashCommandBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('toggle-shiny')
		.setDescription('Patreon Uniquement, activez/désactivez le mode shiny pour votre personnage !'),
	async execute(interaction) {
        let member = interaction.member;
        
        if (!member.roles.cache.some(role => role.name === 'Explorateur (Patreon)')) {
            interaction.reply({
                content: "Vous n'avez pas le rôle Explorateur, rendez vous sur le Patreon pour l'obtenir.",
                ephemeral: true
            });

            return;
        }

        var api_data = new Object()
        api_data.discordUserId = interaction.user.id;

		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/character/toggle-shiny",
			api_data
		)

		try {
			await api_call.connectToAPI();

			if (api_call.getAPIResponseCode() === 200) {
                interaction.reply({
                    content: api_call.getAPIResponseData().get('message'),
                    ephemeral: true
                });
			}
			else {
                interaction.reply({
                    content: "Erreur, veuillez ressayer plus tard.",
                    ephemeral: true
                });
                console.log(api_call.getAPIResponseData().get('message'));
			}
		} catch (error) {
            console.log(error);
            interaction.reply({
                content: "Erreur, veuillez ressayer plus tard.",
                ephemeral: true
            });
		}
	},
};