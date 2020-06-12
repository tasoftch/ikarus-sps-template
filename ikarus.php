<?php
/**
 * This is an example SPS runner file.
 * Your device should execute it to use the SPS.
 *
 * Example:
 *    Raspberry Pi:
 * 		Add the following lines to the /
 */

use Ikarus\SPS\CyclicEngine;
use Ikarus\SPS\Plugin\Cyclic\CallbackCyclicPlugin;
use Ikarus\SPS\Plugin\Cyclic\SetupCallbackPlugin;
use Ikarus\SPS\Plugin\Cyclic\TearDownCallbackPlugin;
use Ikarus\SPS\Plugin\Cyclic\ValueProviderPlugin;
use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;

/// Creates the SPS instance which updates twice per second.
$SPS = new CyclicEngine(2);

$SPS->addPlugin(
	new SetupCallbackPlugin('setup', function() {
		// Do stuff before running the SPS
	})
);

// If you don't add a plugin for an explicit exit of the SPS, it will never exit unless an error occures.
// There are several plugins to explicit stop the SPS:
// Ikarus\SPS\Plugin\Cyclic\StopEngineAfterCycleCountPlugin
// Ikarus\SPS\Plugin\Cyclic\StopEngineAfterIntervalPlugin
// Ikarus\SPS\Plugin\Cyclic\StopEngineAtDatePlugin
// Ikarus\SPS\Plugin\Cyclic\StopEngineIfFileExistsPlugin
// Ikarus\SPS\Plugin\Cyclic\CharInput\StopEngineOnCharacterInput


$SPS->addPlugin(
	// You can provide specific values to the plugin management calculated every cycle.
	new ValueProviderPlugin('my-values', 'my-values', [
		'time' => function() {
			return microtime( true );
		},
		'value' => function() {
			return 'Hello World!';
		}
	])
);

$SPS->addPlugin(
	// After the value provider plugin, the plugin management has the specified values.
	new CallbackCyclicPlugin('test-plugin', function(CyclicPluginManagementInterface $management) {
		echo $management->hasValue('my-domain', 'time'); // 1
		echo $management->fetchValue("my-domain", 'value'); // Hello World!
	})
);

$SPS->addPlugin(
	new \MySPS\Plugin\MyPlugin()
);


$SPS->addPlugin(
	new TearDownCallbackPlugin('tearDown', function() {
		// Do stuff before exiting the SPS

		// Ikarus SPS knows 3 situations which cause a stop of the engine:
		// 1. One of the plugins requires a stop of the engine. The script will continue after the $SPS->run() command.
		// 2. A fatal error occured during running. Again the script will continue.
		// 3. An external signal interruption was triggered to the process (ex: ^C). The script WILL NOT continue
		//    but all tearDown plugins are executed before terminating!
	})
);

// Run the SPS.
$SPS->run();