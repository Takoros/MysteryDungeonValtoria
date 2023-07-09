const chromium = require('chromium');
const { CallingAPI } = require("./CallingAPI.js");
const { AttachmentBuilder } = require('discord.js');
const nodeHtmlToImage = require('node-html-to-image');

async function generateDungeonImage(interaction){
    var api_data = new Object()
    api_data.discordUserId = interaction.user.id;

    var api_call = new CallingAPI(
        interaction.client.env.get("api_host"),
        interaction.client.env.get("api_token"),
        "api/dungeon/instance/show",
        api_data
    )

    await api_call.connectToAPI();

    if (api_call.getAPIResponseCode() === 200) {
        let response = api_call.getAPIResponseData();

        const images = await nodeHtmlToImage({
            html: response.get('htmlContent'),
            type: 'png',
            selector: '#DungeonScreenshot',
            puppeteerArgs: {
                args: ['--no-sandbox'],
                executablePath: chromium.path
            },
            encoding: 'buffer',
        })
        
        return {
            'image' : new AttachmentBuilder(images),
            'webLink' : response.get('webLink'),
            'instanceStatus' : response.get('instanceStatus')
        }
    }
    else {
        return null;
    }
}

module.exports = generateDungeonImage;