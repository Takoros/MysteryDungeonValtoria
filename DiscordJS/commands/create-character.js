const { SlashCommandBuilder, SlashCommandIntegerOption } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

async function prepareCommand(){
    slashCommand = new SlashCommandBuilder()
    .setName('create-character')
    .setDescription('Création du personnage')
    .addStringOption(option =>
        option.setName('nom')
            .setDescription('Nom du personnage')
            .setRequired(true)
            .setMaxLength(30)
    )
    .addStringOption(option =>
        option.setName('genre')
            .setDescription('Genre du personnage')
            .setRequired(true)
            .addChoices(
                { name: 'Mâle', value: 'Mâle' },
                { name: 'Femelle', value: 'Femelle' }
            )
    )
    .addIntegerOption(option =>
        option.setName('age')
            .setDescription('Age du personnage')
            .setRequired(true)
            .setMinValue(18)
            .setMaxValue(44)        
    )
    .addStringOption(option =>
        option.setName('espèce')
            .setDescription('Espèce du personnage')
            .setRequired(true)
    );

    return slashCommand;
}

module.exports = {
	data: prepareCommand(),      
    async execute(interaction) {
        var api_data = new Object()
        api_data.discordUserId = interaction.user.id
        api_data.characterName = interaction.options.getString('nom')
        api_data.characterGender = interaction.options.getString('genre')
        api_data.characterAge = interaction.options.getInteger('age')
        api_data.characterSpeciesName = interaction.options.getString('espèce')

        var api_call = new CallingAPI(
            interaction.client.env.get("api_host"),
            interaction.client.env.get("api_token"),
            "api/character/create",
            api_data,
            interaction
        )
        
        await api_call.connectToAPI();

        if(api_call.getAPIResponseCode() === 200){
            interaction.reply('Personnage Créé !');
        }
    }
};

