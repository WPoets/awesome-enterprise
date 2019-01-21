// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: https://codemirror.net/LICENSE

(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"), require("../xml/xml"), require("../javascript/javascript"), require("../css/css"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror", "../xml/xml", "../javascript/javascript", "../css/css"], mod);
  else // Plain browser env
    mod(CodeMirror);
})(function(CodeMirror) {
  "use strict";
  
	CodeMirror.defineMode('awesome', function (config, parserConfig) {

		var htmlmixedMode = CodeMirror.getMode(config, {
			name: 'htmlmixed',
			multilineTagIndentFactor: parserConfig.multilineTagIndentFactor,
			multilineTagIndentPastTag: parserConfig.multilineTagIndentPastTag
		});

		var shortcodeMode = CodeMirror.getMode(config, {
			name: 'shortcodemixed',	
			htmlMode: true,
			multilineTagIndentFactor: parserConfig.multilineTagIndentFactor,
			multilineTagIndentPastTag: parserConfig.multilineTagIndentPastTag
		});

		function shortcodeToken (stream, state) {
			state.isInShortcode = true;
			var style = shortcodeMode.token(stream, state.shortcodeState);
			var inText = state.shortcodeState.htmlState.tokenize.isInText;
			if (inText && !state.shortcodeState.localState && style === null) {
				state.token = htmlmixedToken;
			} else if (/\]/.test(stream.current())) {
				var cur = stream.current();
				var open = cur.search(/\]/);
				stream.backUp(cur.length - open - 1);
				if (!stream.lineOracle.state.shortcodeState.localMode && (/(\[\/)/.test(stream.string) || /(\/\])/.test(stream.string))){
					state.token = htmlmixedToken;
				}
			}
			return style;
		}

		function htmlmixedToken (stream, state) {
			state.isInShortcode = false;
			var style = htmlmixedMode.token(stream, state.htmlmixedState);
			var inText = state.htmlmixedState.htmlState.tokenize.isInText;
			if (inText && /\[/.test(stream.current()) && !state.htmlmixedState.localState && style === null) {
				var cur = stream.current();
				var open = cur.search(/\[/);
				stream.backUp(cur.length - open);
				if (state.shortcodeState == null) { // ===null or ===undefined
					state.shortcodeState = shortcodeMode.startState(/*htmlmixedMode.indent(state.htmlmixedState, '')*/);
				}
				state.token = shortcodeToken;
			}
			return style;
		}

		return {
			startState: function () {
				var state = htmlmixedMode.startState();
				return {
					token: htmlmixedToken,
					isInShortcode: false,
					shortcodeState: null,
					htmlmixedState: state
				};
			},

			copyState: function (state) {
				var shortcodeStateProx;
				if (state.shortcodeState) {
					shortcodeStateProx = CodeMirror.copyState(shortcodeMode, state.shortcodeState);
				}
				return {
					token: state.token,
					shortcodeState: shortcodeStateProx,
					htmlmixedState: CodeMirror.copyState(htmlmixedMode, state.htmlmixedState)
				};
			},

			token: function (stream, state) {
				return state.token(stream, state);
			},
			/*
			indent: function (state, textAfter,line ) {
				if (!state.isInShortcode) return htmlmixedMode.indent(state.htmlmixedState, textAfter,line);
				else if (state.isInShortcode) return shortcodeMode.indent(state.shortcodeState, textAfter,line);
				else return CodeMirror.Pass;
			},
			*/
			innerMode: function (state) {
				if (state.isInShortcode) {
					return {
						state: state.shortcodeState,
						mode: shortcodeMode
					};
				} else {
					return {
						state: state.htmlmixedState,
						mode: htmlmixedMode
					};
				}
			}
		};
	}, 'htmlmixed', 'shortcodemixed');
	
});