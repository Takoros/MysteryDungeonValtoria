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
			api_data,
            interaction
		)

        await api_call.connectToAPI();

        if (api_call.getAPIResponseCode() === 200) {
            interaction.reply(`Attaque modifiée avec succès !`);
        }
	},
};