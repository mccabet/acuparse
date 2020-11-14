# Acuparse OpenWeather Updater Guide

Open Weather Map is an API based service. You will need a basic understanding of API's to use OpenWaether Map data.

Acuparse will upload your data, but does not facilitate the viewing of that data. You will need to use the provided API
to query your data. See the [Open Weather Map API Guide](https://openweathermap.org/api) for more details!

## Registration

1. Create an account in the members area to generate your API key.
1. Register your station and get a unique identifier.
    - See [OpenWeather](https://openweathermap.org/stations#steps) for more details.

To register your station, you must use your command line or [Postman](https://www.postman.com) to send an API request
to OpenWeather.

Replace `<EXTERNAL_ID>`, `<STATION_NAME>`, `<LATITUDE>`, `<LONGITUDE>`, `<ALTITUDE>`, and `<API_KEY>` with your stations
values in the query below. See the link above for more details.

```bash
curl -X POST -d '{"external_id": "<EXTERNAL_ID>","name": "<STATION_NAME>","latitude": <LATITUDE>,"longitude": <LONGITUDE>,"altitude": <ALTITUDE>, "APPID": <API_KEY>}' 'http://api.openweathermap.org/data/3.0/stations'
```

## Configuration

1. Change enabled to true.
1. Add your station ID and API key.
