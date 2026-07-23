<?php
/**
 * #############################################################
 * ## Home Assistant Commands Plugin for FPP (fpp-haCommands) ##
 * ## Author: jessica12ryan                                   ##
 * ## URL: https://github.com/jessica12ryan/fpp-haCommands    ##
 * #############################################################
 * ## help.php                                                ##
 * #############################################################
 */
?>
<div style="margin:0 auto;">
    <fieldset class="border p-3">
        <legend>HA Commands - Help &amp; Usage Guide</legend>
        <div class="p-3">

            <h3>What This Plugin Does</h3>
            <p>
                This plugin connects FPP to your <b>Home Assistant</b> instance and turns every HA action
                (light.turn_on, switch.turn_off, scene.turn_on, etc.) into an FPP command.
                You can use these commands in playlists <b>between sequences</b>, or embed them <b>inside a sequence</b>
                at exact frames using xLights' FPP Commands timing track.
            </p>

            <hr>

            <h3>Quick Start</h3>
            <ol>
                <li><b>Generate a token</b> in Home Assistant (Profile &rarr; Long-Lived Access Tokens)</li>
                <li><b>Configure</b> the plugin: go to <b>HA Commands</b>, enter your HA URL and token, click <b>Save Settings</b></li>
                <li><b>Test Connection</b> &mdash; verifies HA is reachable</li>
                <li><b>Update Entities</b> &mdash; discovers all your HA actions and generates FPP commands</li>
                <li>Start using the commands in your playlists or sequences!</li>
            </ol>

            <hr>

            <h3>Using in Playlists (Between Sequences)</h3>
            <p>After <b>Update Entities</b>, every HA action appears in the FPP playlist editor as <b>HA - &lt;domain&gt;.&lt;action&gt;</b>.</p>
            <ol>
                <li>Create or edit a playlist</li>
                <li>Add a <b>Command</b> entry</li>
                <li>Select a command starting with <b>HA - </b> from the dropdown</li>
                <li>Choose the <b>entity</b> from the populated dropdown</li>
                <li>Set optional parameters (brightness, color, temperature, etc.)</li>
            </ol>
            <p>This fires the HA call when the playlist reaches that step.</p>

            <hr>

            <h3>Using Inside a Sequence (Frame-Accurate)</h3>
            <p>
                You can fire HA commands at <b>exact frames</b> during a sequence using xLights' built-in
                <b>FPP Commands</b> timing track and FPP <b>Command Presets</b>.
                This is perfect for syncing lights, scenes, or effects to specific moments in your show.
            </p>

            <h4>Step 1: Create FPP Command Presets</h4>
            <p>
                In FPP, go to <b>Commands &rarr; Command Presets</b> and create a preset for each HA action.
                For example, a preset named <b>"Porch Lights On"</b> that calls <b>HA - light.turn_on</b>
                with entity <code>light.porch</code> and brightness 80.
            </p>

            <h4>Step 2: Add an FPP Commands Timing Track in xLights</h4>
            <p>
                In your xLights sequence, right-click on the timing tracks area and choose
                <b>Add Timing Track</b>. Set the track <b>Type</b> to <b>FPP Commands</b>.
            </p>

            <h4>Step 3: Place Command Markers</h4>
            <p>
                Right-click on the FPP Commands track at the desired time and choose <b>Add FPP Command</b>.
                Enter the <b>exact preset name</b> from Step 1 (e.g., <code>Porch Lights On</code>).
                Repeat for every HA action point.
            </p>

            <h4>Step 4: Export &amp; Play</h4>
            <p>
                Render the sequence, upload it to FPP, and add it to a playlist.
                When FPP plays the sequence, it fires the preset at the correct frame &mdash;
                your HA command executes in sync with the show.
            </p>

            <hr>

            <h3>Command Parameters</h3>
            <p>Depending on the domain, commands include extra optional fields:</p>
            <table class="fppTable" style="width: auto;">
                <thead>
                    <tr><th>Domain</th><th>Extra Fields</th></tr>
                </thead>
                <tbody>
                    <tr><td>light</td><td>Brightness %, RGB Color (r,g,b), Color Temp (mireds)</td></tr>
                    <tr><td>cover</td><td>Position %</td></tr>
                    <tr><td>climate</td><td>Temperature, HVAC Mode, Target Temp Low/High</td></tr>
                    <tr><td>fan</td><td>Speed, Direction</td></tr>
                    <tr><td>media_player</td><td>Volume (0.0-1.0), Source</td></tr>
                    <tr><td>vacuum</td><td>Command</td></tr>
                    <tr><td>water_heater</td><td>Temperature</td></tr>
                    <tr><td>humidifier</td><td>Humidity (0-100)</td></tr>
                    <tr><td>scene</td><td>Transition (seconds) &mdash; on <i>turn_on</i> only</td></tr>
                    <tr><td>alarm_control_panel</td><td>Code &mdash; on arm/disarm actions</td></tr>
                    <tr><td>lock</td><td>Code</td></tr>
                    <tr><td>number / input_number</td><td>Value</td></tr>
                    <tr><td>select / input_select</td><td>Option &mdash; on <i>select_option</i> only</td></tr>
                    <tr><td>siren</td><td>Tone, Volume (0.0-1.0), Duration (seconds)</td></tr>
                    <tr><td>time</td><td>Time (HH:MM:SS)</td></tr>
                    <tr><td>timer</td><td>Duration (HH:MM:SS or seconds) — on <i>start</i> and <i>change</i></td></tr>
                    <tr><td>notify</td><td>Message, Title</td></tr>
                    <tr><td>tts</td><td>Message, Language</td></tr>
                    <tr><td>remote</td><td>Command, Device, Repeat Count</td></tr>
                    <tr><td>All others</td><td>Extra JSON field for any additional data</td></tr>
                </tbody>
            </table>

            <h4>Extra JSON Field</h4>
            <p>
                Every command includes an optional <b>Extra JSON</b> field for parameters not covered by the built-in fields.
                Enter valid JSON:
            </p>
            <pre>{"rgb_color": [255, 0, 0], "effect": "colorloop", "transition": 2}</pre>

            <hr>

            <h3>HA - Get State Command</h3>
            <p>
                A special <b>HA - Get State</b> command is always available. It fetches an entity's current state and
                writes it to the FPP log. Useful for debugging or triggering conditional logic.
            </p>

            <hr>

            <h3>Troubleshooting</h3>

            <h4>Commands not appearing in the playlist editor?</h4>
            <ul>
                <li>Make sure you clicked <b>Update Entities</b> after configuring HA</li>
                <li>Check that FPPD restarted (the plugin does this automatically after updating)</li>
                <li>Verify your HA token has not expired (regenerate in HA profile if needed)</li>
            </ul>

            <h4>Command fails when the playlist runs?</h4>
            <p>Check the FPPD log for HA plugin errors:</p>
            <pre>tail -20 /home/fpp/media/logs/plugin-fpp-haCommands.log</pre>

            <h4>Need to refresh after adding/removing HA entities?</h4>
            <p>
                Go back to <b>HA Commands</b> and click <b>Update Entities</b>. This re-fetches all entities and
                actions from HA and regenerates the command list. FPPD restarts automatically.
            </p>

            <h4>Need more help?</h4>
            <p>
                Visit the <a href="https://github.com/jessica12ryan/fpp-haCommands" target="_blank">GitHub repository</a>
                for the full README, issue tracker, and community discussions.
            </p>
        </div>
    </fieldset>
</div>
