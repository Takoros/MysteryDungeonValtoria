const { SlashCommandBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('modify-rotation-attack')
		.setDescription('Modifiez les attaques de vos rotations !')
        .addStringOption(option =>
            option.setName('type')
                .setDescription("Rotation ou Ouverture ?")
                .setRequired(true)
				.addChoices(
                    { name: 'Ouverture', value: 'Opener' },
                    { name: 'Rotation', value: 'Rotation' }
                )
        )
        .addStringOption(option =>
            option.setName('name')
                .setDescription("Nom de l'attaque")
                .setRequired(true)
                .setMaxLength(30)
        )
        .addIntegerOption(option =>
            option.setName('slot')
                .setDescription("Emplacement à remplacer (1,2,3,4 ou 5)")
                .setRequired(true)
                .setMinValue(1)
                .setMaxValue(5)
        ),
	async execute(interaction) {
		var api_data = new Object()
		api_data.discordUserId = interaction.user.id;
		api_data.rotationType = interaction.options.getString('type');
        api_data.attackName = interaction.options.getString('name');
        api_data.attackSlot = interaction.options.getInteger('slot');

		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/character/modify/attack",
			api_data
		)

		try {
			await api_call.connectToAPI();

			if (api_call.getAPIResponseCode() === 200) {
                interaction.reply(`Attaque modifiée avec succès !`);
			}
            else if(api_call.getAPIResponseData().get('message') === 'This Rotation does not have enough Action Point left to change this slot with this attack'){
                interaction.reply(`Vous n'avez pas assez de PA restant pour ajouter cette attaque sur cet emplacement.`);
            }
            else if(api_call.getAPIResponseData().get('message') === 'AttackId does not relate to any Attack'){
                interaction.reply(`Attaque introuvable.`);
            }
            else if(api_call.getAPIResponseData().get('message') === 'This Attack is not available for this character'){
                interaction.reply(`Vous n'avez pas accès à cette attaque !`);
            }
			else {
                interaction.reply('Erreur, veuillez réessayer plus tard.');
			}

		} catch (error) {
			interaction.reply('Erreur, veuillez réessayer plus tard.');
		}
	},
};