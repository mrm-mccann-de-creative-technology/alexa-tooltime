# CPW Season Calendar Alexa Skills

HTTP Endpoint

`https://apps.mrm.de/cpw-season-calendar/`

Invocation Name:

`Season calendar`

This is a linear app. You just open it and listen and answer what she asks you.


Intent Schema:

```json
{
  "intents": [
    {
      "intent": "AMAZON.CancelIntent"
    },
    {
      "intent": "AMAZON.HelpIntent"
    },
    {
      "intent": "AMAZON.StopIntent"
    },
    {
      "intent": "AMAZON.YesIntent"
    },
    {
      "slots": [
        {
          "name": "fruit",
          "type": "fruit"
        }
      ],
      "intent": "chooseFruit"
    },
    {
      "slots": [
        {
          "name": "recipeNr",
          "type": "recipeNr"
        }
      ],
      "intent": "chooseRecipe"
    }
  ]
}
```

Slots:
```
Fruit: blueberry | raspberry | strawberry
RecipeNr: 1 | 2 | 3
```
