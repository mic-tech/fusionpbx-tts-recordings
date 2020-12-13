# Install HOW TO
1. ```cd /usr/src/```
2. ```git clone https://github.com/mic-tech/fusionpbx-app-tts-recordings tts_recordings```
3. ```cp -R tts_recordings /var/www/fusionpbx/app/```
4. Go to GUI
5. Upgrades -> SCHEMA; APP DEFAULTS; MENU DEFAULTS; PERMISSION DEFAULTS
6. Log out and back in
7. Advanced -> Default Settings -> Tts Recordings
8. Set azure_subscription_key and azure_region to your Microsoft Azure Cognitive Speech Services subscription key and the region you wish to use for its API requests respectively
9. Go to Apps -> TTS Recordings -> Add
10. Enter the recording options and the text to speak, click the play button to preview and customize to your satisfaction
11. Save, now the recording is available to be used as a recording in FusionPBX, go ahead and assign it to any IVR and test it out
12. TTS recordings are saved in the same directory as FusionPBX recordings, or saved in the database directly if FusionPBX is configured that way

TTS recordings show up in the Apps -> Recordings as well, this is by design. If you happen to delete a TTS recording from the Apps -> Recordings and want to restore it, just go to Apps -> TTS Recordings, find it in the list and edit it, save it and it will be recreated.