// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"), require("../../addon/mode/simple"), require("../../addon/mode/multiplex"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror", "../../addon/mode/simple", "../../addon/mode/multiplex"], mod);
  else // Plain browser env
    mod(CodeMirror);
})(function(CodeMirror) {
  "use strict";

  CodeMirror.defineSimpleMode("awcode-tags", {
    start: [
      { regex: /\/\/\*\*/, push: "dash_comment", token: "comment" },
      { regex: /\[[a-zA-Z0-9_]+\.([@A-Za-z_0-9]\w*)/,    push: "awcode", token: "tag" },
      { regex: /\[\/[a-zA-Z0-9_]+\.([@A-Za-z_0-9]\w*)/,    push: "awcode_close", token: "tag" },
	   { regex: /\[[a-zA-Z0-9 ]\w*/,    push: "defs", token: "def" }
    ],
	defs: [
		  { regex: /]/, pop: true, token: "def" },
		  { regex: /\/]/, pop: true, token: "def" },
		  
		  { regex: /{{[a-zA-Z0-9 \.\-_]*}}/, token: "variable" },
		  { regex: /{[a-zA-Z0-9\.\-_]*}/, token: "variable" },
		  // Double and single quotes
		  { regex: /"(?:[^\\"]|\\.)*"?/, token: "string" },
		  { regex: /'(?:[^\\']|\\.)*'?/, token: "string" },
		  // Atoms like = and .
		  { regex: /=|~|@|true|false/, token: "atom" },

		  // Paths
		  { regex: /(?:\.\.\/)*(?:[A-Za-z_][\w\.]*)+/, token: "variable-2" }
	],
	awcode_close: [
		  { regex: /]/, pop: true, token: "tag" }
	],
    awcode: [
      { regex: /]/, pop: true, token: "tag" },
      { regex: /\/]/, pop: true, token: "tag" },
	  { regex: /{{[a-zA-Z0-9 \.\-_]*}}/, token: "variable" },
	  { regex: /{[a-zA-Z0-9\.\-_]*}/, token: "variable" },
  
      // Double and single quotes
      { regex: /"(?:[^\\"]|\\.)*"?/, token: "string" },
      { regex: /'(?:[^\\']|\\.)*'?/, token: "string" },

      // awcode keywords
      { regex: /(?:else|this)\b/, token: "keyword" },

      // Numeral
      { regex: /\d+/i, token: "number" },

      // Atoms like = and .
      { regex: /=|~|@|true|false/, token: "atom" },

      // Paths
      { regex: /(?:\.\.\/)*(?:[A-Za-z_][\w\.]*)+/, token: "variable-2" }
    ],
    dash_comment: [
      { regex: /\*\*\/\//, pop: true, token: "comment" },

      // Commented code
      { regex: /./, token: "comment"}
    ]
  });

  CodeMirror.defineMode("awcode", function(config, parserConfig) {
    var awcode = CodeMirror.getMode(config, "awcode-tags");
    if (!parserConfig || !parserConfig.base) return awcode;
    return CodeMirror.multiplexingMode(
      CodeMirror.getMode(config, parserConfig.base),
      {open: "[", close: "]", mode: awcode, parseDelimiters: true}
    );
  });

  CodeMirror.defineMIME("text/awcode", "awcode");
});
