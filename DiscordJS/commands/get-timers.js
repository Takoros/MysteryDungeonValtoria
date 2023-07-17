const { SlashCommandBuilder, EmbedBuilder, AttachmentBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");
const { displayCooldown } = require('../functions/displayTools.js');

module.exports = {
	data: new SlashCommandBuilder()
		.setName('get-timers')
		.setDescription("Obtenez les temps de récupérations sur les différentes activitées."),
	async execute(interaction) {
		var api_data = new Object()
        api_data.discordUserId = interaction.user.id;

		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/character/timers",
			api_data,
            interaction
		)

		await api_call.connectToAPI();
        
		if (api_call.getAPIResponseCode() === 200) {
            data = api_call.getAPIResponseData().get('data');

            const hourglassIcon = new AttachmentBuilder('./assets/hourglass.gif');
            const timersEmbed = new EmbedBuilder().setAuthor({ name: `Vos temps de récupération`, iconURL: 'attachment://hourglass.gif'})
                                                  .addFields(
                                                        {name: '<:dungeon:1130497785704226836> Donjon :', value : displayCooldown(data.dungeonTimer,'COOLDOWN_FULL_TIME')}
                                                    );

            interaction.reply({
                embeds: [timersEmbed],
                files: [hourglassIcon],
                ephemeral: true
            });
		}
	},
};