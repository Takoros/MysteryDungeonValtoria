const { SlashCommandBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('spend-stats-points')
		.setDescription('Dépensez vos points de statistiques !')
        .addStringOption(option =>
            option.setName('statistique')
                .setDescription("Statistique à améliorer")
                .setRequired(true)
                .addChoices(
                    { name: 'Vitalité', value: 'vitality' },
                    { name: 'Force', value: 'strength' },
                    { name: 'Pouvoir', value: 'power' },
                    { name: 'Présence', value: 'presence' },
                    { name: 'Endurance', value: 'stamina' },
                    { name: 'Courage', value: 'bravery' },
                    { name: 'Impassibilité', value: 'impassiveness' },
                    { name: 'Coordination', value: 'coordination' },
                    { name: 'Agilité', value: 'agility' },
                    { name: 'Vitesse', value: 'speed' }
                )
        )
        .addIntegerOption(option =>
            option.setName('montant')
                .setDescription("Montant de points à dépenser")
                .setRequired(true)
                .setMinValue(1)
        ),
	async execute(interaction) {
		var api_data = new Object()
		api_data.discordUserId = interaction.user.id;
        api_data.statToIncrease = interaction.options.getString('statistique');
        api_data.amountOfPointsSpent = interaction.options.getInteger('montant');

		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/character/spend/statPoint",
			api_data
		)

		try {
			await api_call.connectToAPI();

            if (api_call.getAPIResponseCode() === 200) {
                interaction.reply(`Statistique augmentée avec succès !`);
			}
            else if(api_call.getAPIResponseData().get('message') === 'Character does not have enough statPoints'){
                interaction.reply(`Vous n'avez pas assez de points de statistiques !`);
            }
			else {
                interaction.reply('Erreur, veuillez réessayer plus tard.');
			}
		} catch (error) {
			interaction.reply('Erreur, veuillez réessayer plus tard.');
		}
	},
};