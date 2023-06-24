const { SlashCommandBuilder } = require('discord.js');
const { AttachmentBuilder, EmbedBuilder } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

module.exports = {
	data: new SlashCommandBuilder()
		.setName('list-playable-species')
		.setDescription("Liste l'intégralité des espèces jouables."),
	async execute(interaction) {
		var api_data = new Object()
		var api_call = new CallingAPI(
			interaction.client.env.get("api_host"),
			interaction.client.env.get("api_token"),
			"api/data/list_species",
			api_data
		)
		await api_call.connectToAPI()
        if (api_call.getAPIResponseCode() !== 200) {
			console.log(`Code ${api_call.getAPIResponseCode()} : Error while performing request`);
            await interaction.reply('Erreur, veuillez réessayer plus tard');
        }
        else {
			const pawIcon = new AttachmentBuilder('./assets/paw.png');
            const exampleEmbed = new EmbedBuilder().setAuthor({ name: 'Liste des espèces jouables', iconURL: 'attachment://paw.png'});

			api_call.getAPIResponseData().forEach(pokemon => {
				if(pokemon.isPlayable == true){
					exampleEmbed.addFields({name: `#${pokemon.id}`, value: `${pokemon.name} (${preparePokemonTypeDisplay(pokemon.types)})`, inline: true,})
				}
			});

            await interaction.reply({ embeds: [exampleEmbed], files: [pawIcon] });
        }
	},
};

function preparePokemonTypeDisplay(types)
{
	typesNames = '';

	types.forEach(function(type, idx, array){
		if(idx === array.length - 1){
			typesNames += type.name;
		}
		else {
			typesNames += type.name+'|';
		}
	});

	return typesNames;
}