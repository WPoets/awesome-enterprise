<?php

namespace aw2\code\sample;

\aw2_library::add_service('code.sample', 'Generate code samples with expected and actual output', ['func'=>'code_sample', 'namespace'=>__NAMESPACE__]);

function code_sample($atts, $content=null, $shortcode) {
    if(!isset($shortcode['tags_left'][0])) {
        throw new \InvalidArgumentException('code.sample: You must specify a context name starting with @');
    }
    
    $context = $shortcode['tags_left'][0];
    
    if(strpos($context, '@') !== 0) {
        throw new \Exception('code.sample: Context name must start with @');
    }
    
    $stack_id = \aw2\call_stack\push_context('code_sample', $context, $context);
    $call_stack = &\aw2_library::get_array_ref('call_stack', $stack_id);

    $backup_service = &\aw2_library::get_array_ref('handlers', $context);
    
    \aw2_library::add_service($context, 'code.sample context handler', ['func'=>'code_sample_context_handler', 'namespace'=>'aw2\code_sample_context']);

    \aw2_library::parse_shortcode($content);
    
    $data = \aw2\common\env\get('data', $context);
    $title = \aw2\common\env\get('title', $context);
    $desc = \aw2\common\env\get('desc', $context);
    $code = \aw2\common\env\get('code', $context);
    $expected_result = \aw2\common\env\get('expected_result', $context);

	$context_ref=&\aw2_library::get_array_ref($context);
	
	if(isset($atts['show_data'])) {
		$context_ref['show_data'] = $atts['show_data'];
	}

    $scheme='tabs';
	if(isset($atts['scheme'])) {
		$scheme = $atts['scheme'];
	}
 
    $schemes=array();

$schemes['vertical'] = <<<AWESOME
    <div id="{#_context_#.id}" class="code-sample-block">
        <h2>[content.run content:@=#_context_#.title /]</h2>
        [content.run content:@=#_context_#.description /]

        [if.equal lhs={#_context_#.show_data} rhs=yes]
        <div class="code-sample-section">
            <div class="code-sample-header" data-section="data">
                <span>Sample Data</span><span class="toggle-icon">▼</span>
            </div>
            <div class="code-sample-content">
                <pre>[env.dump #_context_#.data /]</pre>
            </div>
        </div>
        [/if.equal]
        
        <div class="code-sample-section">
            <div class="code-sample-header" data-section="code">
                <span>Code</span><span class="toggle-icon">▼</span>
            </div>
            <div class="code-sample-content">
                <pre class="language-awesome">[env.get #_context_#.code /]</pre>
            </div>
        </div>

        <div class="code-sample-section">
            <div class="code-sample-header" data-section="expected">
                <span>Expected Output</span><span class="toggle-icon">▼</span>
            </div>
            <div class="code-sample-content">
                <pre>[content.run content:@=#_context_#.expected_result /]</pre>
            </div>
        </div>

        <div class="code-sample-section">
            <div class="code-sample-header" data-section="actual">
                <span>Actual Output</span><span class="toggle-icon">▼</span>
            </div>
            <div class="code-sample-content">
                <pre>[content.run content:@=#_context_#.code /]</pre>
            </div>
        </div>

	</div>	
AWESOME;


$schemes['split'] = <<<AWESOME
 <div id="{#_context_#}" class="code-sample-block">
        <h2>[content.run content:@=#_context_#.title /]</h2>
        [content.run content:@=#_context_#.description /]
        
        <!-- Code Section -->
        <div class="code-section">
            <div class="section-header">Code</div>
            <div class="code-container">
                <button class="copy-btn" onclick="copyCode('{#_context_#}-code-content')">
                    <span class="copy-icon">📋</span> Copy
                </button>
                <pre id="{#_context_#}-code-content" class="language-awesome">[env.get #_context_#.code /]</pre>
            </div>
        </div>
        
        <!-- Output Section -->
        <div class="output-section">
            <div class="section-header">Output</div>
            <table class="output-table">
                <tr>
                    <th class="w-1/2">Expected Output</th>
                    <th class="w-1/2">Actual Output</th>
                </tr>
                <tr>
                    <td class="align-top">
                        <pre>[content.run content:@=#_context_#.expected_result /]</pre>
                    </td>
                    <td class="align-top">
                        <pre>[content.run content:@=#_context_#.code /]</pre>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <style>
        .code-sample-block {
            margin: 20px 0;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 500;
            color: #1e293b;
        }

        .code-section, .output-section {
            margin-bottom: 24px;
        }
        
        .code-container {
            position: relative;
            padding: 16px;
        }
        
        .output-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 16px;
        }
        
        .output-table th {
            text-align: left;
            font-weight: 500;
            padding-bottom: 8px;
            color: #1e293b;
        }
        
        pre {
            background: #f3f4f6;
            padding: 16px;
            border-radius: 4px;
            overflow: auto;
            margin: 0;
        }

        .copy-btn {
            position: absolute;
            top: 24px;
            right: 24px;
            padding: 6px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 14px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .copy-btn:hover {
            opacity: 1;
        }

        .copy-icon {
            font-size: 16px;
        }

        .copy-btn.copied {
            background: #22c55e;
            color: white;
            border-color: #22c55e;
        }
    </style>

    <script>
        function copyCode(elementId) {
            const codeElement = document.getElementById(elementId);
            const button = codeElement.parentElement.querySelector('.copy-btn');
            const text = codeElement.textContent;

            // Copy text to clipboard
            navigator.clipboard.writeText(text).then(() => {
                // Show success state
                button.classList.add('copied');
                button.innerHTML = '<span class="copy-icon">✓</span> Copied!';
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    button.classList.remove('copied');
                    button.innerHTML = '<span class="copy-icon">📋</span> Copy';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                button.textContent = 'Failed to copy';
            });
        }
    </script>
AWESOME;

$schemes['tabs'] = <<<AWESOME
<div id="{#_context_#}" class="code-sample-block">
        <h2>[content.run content:@=#_context_#.title /]</h2>
        [content.run content:@=#_context_#.description /]
        
        <!-- Tabs -->
        <div class="code-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="switchTab(this, '{#_context_#}-code')">Code</button>
                <button class="tab-btn" onclick="switchTab(this, '{#_context_#}-output')">Output</button>
            </div>
            
            <!-- Code Tab -->
            <div id="{#_context_#}-code" class="tab-content active">
                <div class="code-container">
                    <button class="copy-btn" onclick="copyCode('{#_context_#}-code-content')">
                        <span class="copy-icon">📋</span> Copy
                    </button>
                    <pre id="{#_context_#}-code-content" class="language-awesome">[env.get #_context_#.code /]</pre>
                </div>
            </div>
            
            <!-- Output Tab -->
            <div id="{#_context_#}-output" class="tab-content hidden">
                <table class="output-table">
                    <tr>
                        <th class="w-1/2">Expected Output</th>
                        <th class="w-1/2">Actual Output</th>
                    </tr>
                    <tr>
                        <td class="align-top">
                            <pre>[content.run content:@=#_context_#.expected_result /]</pre>
                        </td>
                        <td class="align-top">
                            <pre>[content.run content:@=#_context_#.code /]</pre>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <style>
        .code-sample-block {
            margin: 20px 0;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .code-tabs {
            padding: 16px;
        }
        
        .tab-buttons {
            margin-bottom: 16px;
        }
        
        .tab-btn {
            padding: 8px 16px;
            border: none;
            background: none;
            cursor: pointer;
            margin-right: 8px;
        }
        
        .tab-btn.active {
            border-bottom: 2px solid #2563eb;
            color: #2563eb;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-content.hidden {
            display: none;
        }
        
        .output-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 16px;
        }
        
        .output-table th {
            text-align: left;
            font-weight: 500;
            padding-bottom: 8px;
        }
        
        pre {
            background: #f3f4f6;
            padding: 16px;
            border-radius: 4px;
            overflow: auto;
            margin: 0;
        }

        .code-container {
            position: relative;
        }

        .copy-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            padding: 6px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 14px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .copy-btn:hover {
            opacity: 1;
        }

        .copy-icon {
            font-size: 16px;
        }

        .copy-btn.copied {
            background: #22c55e;
            color: white;
            border-color: #22c55e;
        }
    </style>

    <script>
        function switchTab(button, tabId) {
            // Get parent tabs container
            const tabsContainer = button.closest('.code-tabs');
            
            // Hide all tab contents
            tabsContainer.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
                tab.classList.add('hidden');
            });
            
            // Show selected tab content
            const selectedTab = document.getElementById(tabId);
            selectedTab.classList.remove('hidden');
            selectedTab.classList.add('active');
            
            // Update button states
            tabsContainer.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            button.classList.add('active');
        }

        function copyCode(elementId) {
            const codeElement = document.getElementById(elementId);
            const button = codeElement.parentElement.querySelector('.copy-btn');
            const text = codeElement.textContent;

            // Copy text to clipboard
            navigator.clipboard.writeText(text).then(() => {
                // Show success state
                button.classList.add('copied');
                button.innerHTML = '<span class="copy-icon">✓</span> Copied!';
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    button.classList.remove('copied');
                    button.innerHTML = '<span class="copy-icon">📋</span> Copy';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                button.textContent = 'Failed to copy';
            });
        }
    </script>
AWESOME;

$schemes['cards'] = <<<AWESOME
<div id="{#_context_#}" class="code-sample-block">
        <h2>[content.run content:@=#_context_#.title /]</h2>
        [content.run content:@=#_context_#.description /]
        
        <div class="panels-container">
            <!-- Left Panel - Code -->
            <div class="panel code-panel">
                <div class="panel-header">
                    <div class="header-text">Code</div>
                    <button class="copy-btn" onclick="copyCode('{#_context_#}-code-content')">
                        <span class="copy-icon">📋</span> Copy
                    </button>
                </div>
                <div class="panel-content">
                    <pre id="{#_context_#}-code-content" class="language-awesome">[env.get #_context_#.code /]</pre>
                </div>
            </div>
            
            <!-- Right Panel - Output -->
            <div class="panel output-panel">
                <div class="panel-header">
                    <div class="header-text">Output</div>
                </div>
                <div class="panel-content">
                    <div class="output-section">
                        <div class="output-header">Expected</div>
                        <pre>[content.run content:@=#_context_#.expected_result /]</pre>
                    </div>
                    <div class="output-divider"></div>
                    <div class="output-section">
                        <div class="output-header">Actual</div>
                        <pre>[content.run content:@=#_context_#.code /]</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .code-sample-block {
            margin: 20px 0;
        }

        .panels-container {
            display: flex;
            gap: 20px;
            margin-top: 16px;
        }

        .panel {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 16px;
            font-weight: 500;
            color: #1e293b;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-content {
            flex: 1;
            overflow: auto;
            padding: 16px;
        }

        .output-section {
            flex: 1;
        }

        .output-header {
            font-weight: 500;
            color: #64748b;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .output-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 16px 0;
        }
        
        pre {
            background: #f3f4f6;
            padding: 16px;
            border-radius: 4px;
            overflow: auto;
            margin: 0;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .copy-btn {
            padding: 6px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 14px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .copy-btn:hover {
            opacity: 1;
        }

        .copy-icon {
            font-size: 16px;
        }

        .copy-btn.copied {
            background: #22c55e;
            color: white;
            border-color: #22c55e;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .panels-container {
                flex-direction: column;
            }

            .panel {
                width: 100%;
            }
        }
    </style>

    <script>
        function copyCode(elementId) {
            const codeElement = document.getElementById(elementId);
            const button = codeElement.parentElement.parentElement.querySelector('.copy-btn');
            const text = codeElement.textContent;

            // Copy text to clipboard
            navigator.clipboard.writeText(text).then(() => {
                // Show success state
                button.classList.add('copied');
                button.innerHTML = '<span class="copy-icon">✓</span> Copied!';
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    button.classList.remove('copied');
                    button.innerHTML = '<span class="copy-icon">📋</span> Copy';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                button.textContent = 'Failed to copy';
            });
        }
    </script>
AWESOME;


	$awesome_code = str_replace('#_context_#', $context, $schemes[$scheme]);


	$output=\aw2_library::parse_shortcode($awesome_code);



    \aw2\call_stack\pop_context($stack_id);
    \aw2\common\env\set($context, $backup_service, 'handlers');
    
    return $output;
}

namespace aw2\code_sample_context;

function code_sample_context_handler($atts, $content=null, $shortcode) {
    $service = 'code_sample_context.' . implode('.', $shortcode['tags_left']);
    $atts['@context'] = $shortcode['tags'][0];
    \aw2_library::service_run($service, $atts, $content);
}

\aw2_library::add_service('code_sample_context.data', 'Save the sample data', ['namespace'=>__NAMESPACE__]);

function data($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    if(!\aw2\call_stack\is_within_context('code_sample:' . $context)) {
        throw new \OutOfBoundsException('You are accessing in a different context');
    }

    if(empty($content)) {
        \aw2\common\env\set('data', array(), $context);
        return '';
    }

    $ab = new \array_builder();
    $data = $ab->parse($content);
    
    \aw2\common\env\set('data', $data, $context);
    return '';
}

\aw2_library::add_service('code_sample_context.title', 'Save the sample title', ['namespace'=>__NAMESPACE__]);

function title($atts, $content=null, $shortcode) {

    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    if(!\aw2\call_stack\is_within_context('code_sample:' . $context)) {
        throw new \OutOfBoundsException('You are accessing in a different context');
    }

    if($content === null) {
        throw new \InvalidArgumentException('Title content cannot be null');
    }
    
    \aw2\common\env\set('title', $content, $context);
    return '';
}

\aw2_library::add_service('code_sample_context.description', 'Save the sample description', ['func'=>'description', 'namespace'=>__NAMESPACE__]);

function description($atts, $content=null, $shortcode) {

    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    if(!\aw2\call_stack\is_within_context('code_sample:' . $context)) {
        throw new \OutOfBoundsException('You are accessing in a different context');
    }
    
    \aw2\common\env\set('description', empty($content) ? '' : $content, $context);
    return '';
}

\aw2_library::add_service('code_sample_context.code', 'Save the sample code', ['namespace'=>__NAMESPACE__]);

function code($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    if(!\aw2\call_stack\is_within_context('code_sample:' . $context)) {
        throw new \OutOfBoundsException('You are accessing in a different context');
    }

    if($content === null) {
        throw new \InvalidArgumentException('Code content cannot be null');
    }
    
    \aw2\common\env\set('code', $content, $context);
    return '';
}

\aw2_library::add_service('code_sample_context.expected_result', 'Save the expected result', ['namespace'=>__NAMESPACE__]);

function expected_result($atts, $content=null, $shortcode) {
    if(!isset($atts['@context'])) {
        throw new \InvalidArgumentException('@context is missing');
    }
    
    $context = $atts['@context'];
    
    if(!\aw2\call_stack\is_within_context('code_sample:' . $context)) {
        throw new \OutOfBoundsException('You are accessing in a different context');
    }

    \aw2\common\env\set('expected_result', empty($content) ? '' : $content, $context);

    return '';
}
