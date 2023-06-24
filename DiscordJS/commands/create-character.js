const { SlashCommandBuilder, SlashCommandIntegerOption } = require('discord.js');
const { CallingAPI } = require("../functions/CallingAPI.js");

/*
Création d'un personnage :
"discordUserId" : String,
"characterName" : String max 30char
"characterGender" : String (Male | Female)
"characterAge" : INT
"characterSpeciesId" : INT
*/

function prepareCharacterSpeciesChoices(){
    return (async () => {
        try {
            var api_data = new Object();
            var api_call = new CallingAPI(
                process.env.DEV_API_HOST,
                process.env.DEV_ZAOS_TOKEN,
                "api/data/list_species",
                api_data
            );
            await api_call.connectToAPI();
        
            return api_call.getAPIResponseData();
        } catch (error) {
            return [];
        }
    })();
}

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
    );
    
    specieList = await prepareCharacterSpeciesChoices().then(function(results){return results})

    speciesOption = new SlashCommandIntegerOption().setName('espèce')
                                                   .setDescription('Espèce du personnage')
                                                   .setRequired(true);

    specieList.forEach(pokemon => {
        speciesOption.addChoices({name: pokemon.name, value: pokemon.id})
    });

    slashCommand.addIntegerOption(speciesOption);

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
        api_data.characterSpeciesId = interaction.options.getInteger('espèce')

        var api_call = new CallingAPI(
            interaction.client.env.get("api_host"),
            interaction.client.env.get("api_token"),
            "api/character/create",
            api_data
        )
        await api_call.connectToAPI()

        if(api_call.getAPIResponseCode() === 201){
            interaction.reply('Personnage Créé !');
        }
        else if(api_call.getAPIResponseCode() === 400 && api_call.getAPIResponseData().get("message") === 'User already have a character.'){
            interaction.reply('Vous possédez déjà un personnage.');
        }
        else {
            interaction.reply('Erreur, veuillez réessayer plus tard.');
        }
    }
};

