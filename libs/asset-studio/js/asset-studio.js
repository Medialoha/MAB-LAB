/*
Copyright 2010 Google Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

/*
	Base.js, version 1.1
	Copyright 2006-2007, Dean Edwards
	License: http://www.opensource.org/licenses/mit-license.php
*/

var Base = function() {
	// dummy
};

Base.extend = function(_instance, _static) { // subclass
	var extend = Base.prototype.extend;
	
	// build the prototype
	Base._prototyping = true;
	var proto = new this;
	extend.call(proto, _instance);
	delete Base._prototyping;
	
	// create the wrapper for the constructor function
	//var constructor = proto.constructor.valueOf(); //-dean
	var constructor = proto.constructor;
	var klass = proto.constructor = function() {
		if (!Base._prototyping) {
			if (this._constructing || this.constructor == klass) { // instantiation
				this._constructing = true;
				constructor.apply(this, arguments);
				delete this._constructing;
			} else if (arguments[0] != null) { // casting
				return (arguments[0].extend || extend).call(arguments[0], proto);
			}
		}
	};
	
	// build the class interface
	klass.ancestor = this;
	klass.extend = this.extend;
	klass.forEach = this.forEach;
	klass.implement = this.implement;
	klass.prototype = proto;
	klass.toString = this.toString;
	klass.valueOf = function(type) {
		//return (type == "object") ? klass : constructor; //-dean
		return (type == "object") ? klass : constructor.valueOf();
	};
	extend.call(klass, _static);
	// class initialisation
	if (typeof klass.init == "function") klass.init();
	return klass;
};

Base.prototype = {	
	extend: function(source, value) {
		if (arguments.length > 1) { // extending with a name/value pair
			var ancestor = this[source];
			if (ancestor && (typeof value == "function") && // overriding a method?
				// the valueOf() comparison is to avoid circular references
				(!ancestor.valueOf || ancestor.valueOf() != value.valueOf()) &&
				/\bbase\b/.test(value)) {
				// get the underlying method
				var method = value.valueOf();
				// override
				value = function() {
					var previous = this.base || Base.prototype.base;
					this.base = ancestor;
					var returnValue = method.apply(this, arguments);
					this.base = previous;
					return returnValue;
				};
				// point to the underlying method
				value.valueOf = function(type) {
					return (type == "object") ? value : method;
				};
				value.toString = Base.toString;
			}
			this[source] = value;
		} else if (source) { // extending with an object literal
			var extend = Base.prototype.extend;
			// if this object has a customised extend method then use it
			if (!Base._prototyping && typeof this != "function") {
				extend = this.extend || extend;
			}
			var proto = {toSource: null};
			// do the "toString" and other methods manually
			var hidden = ["constructor", "toString", "valueOf"];
			// if we are prototyping then include the constructor
			var i = Base._prototyping ? 0 : 1;
			while (key = hidden[i++]) {
				if (source[key] != proto[key]) {
					extend.call(this, key, source[key]);

				}
			}
			// copy each of the source object's properties to this object
			for (var key in source) {
				if (!proto[key]) extend.call(this, key, source[key]);
			}
		}
		return this;
	},

	base: function() {
		// call this method from any other method to invoke that method's ancestor
	}
};

// initialise
Base = Base.extend({
	constructor: function() {
		this.extend(arguments[0]);
	}
}, {
	ancestor: Object,
	version: "1.1",
	
	forEach: function(object, block, context) {
		for (var key in object) {
			if (this.prototype[key] === undefined) {
				block.call(context, object[key], key, object);
			}
		}
	},
		
	implement: function() {
		for (var i = 0; i < arguments.length; i++) {
			if (typeof arguments[i] == "function") {
				// if it's a function, call it
				arguments[i](this.prototype);
			} else {
				// add the interface using the extend method
				this.prototype.extend(arguments[i]);
			}
		}
		return this;
	},
	
	toString: function() {
		return String(this.valueOf());
	}
});

(function() {

var imagelib = {};

// A modified version of bitmap.js from http://rest-term.com/archives/2566/

var Class = {
  create : function() {
    var properties = arguments[0];
    function self() {
      this.initialize.apply(this, arguments);
    }
    for(var i in properties) {
      self.prototype[i] = properties[i];
    }
    if(!self.prototype.initialize) {
      self.prototype.initialize = function() {};
    }
    return self;
  }
};

var ConvolutionFilter = Class.create({
  initialize : function(matrix, divisor, bias, separable) {
    this.r = (Math.sqrt(matrix.length) - 1) / 2;
    this.matrix = matrix;
    this.divisor = divisor;
    this.bias = bias;
    this.separable = separable;
  },
  apply : function(src, dst) {
    var w = src.width, h = src.height;
    var srcData = src.data;
    var dstData = dst.data;
    var di, si, idx;
    var r, g, b;

    //if (this.separable) {
      // TODO: optimize if linearly separable ... may need changes to divisor
      // and bias calculations
    //} else {
      // Not linearly separable
      for(var y=0;y<h;++y) {
        for(var x=0;x<w;++x) {
          idx = r = g = b = 0;
          di = (y*w + x) << 2;
          for(var ky=-this.r;ky<=this.r;++ky) {
            for(var kx=-this.r;kx<=this.r;++kx) {
              si = (Math.max(0, Math.min(h - 1, y + ky)) * w +
                    Math.max(0, Math.min(w - 1, x + kx))) << 2;
              r += srcData[si]*this.matrix[idx];
              g += srcData[si + 1]*this.matrix[idx];
              b += srcData[si + 2]*this.matrix[idx];
              //a += srcData[si + 3]*this.matrix[idx];
              idx++;
            }
          }
          dstData[di] = r/this.divisor + this.bias;
          dstData[di + 1] = g/this.divisor + this.bias;
          dstData[di + 2] = b/this.divisor + this.bias;
          //dstData[di + 3] = a/this.divisor + this.bias;
          dstData[di + 3] = 255;
        }
      }
    //}
    // for Firefox
    //dstData.forEach(function(n, i, arr) { arr[i] = n<0 ? 0 : n>255 ? 255 : n; });
  }
});/*
 * glfx.js
 * http://evanw.github.com/glfx.js/
 *
 * Copyright 2011 Evan Wallace
 * Released under the MIT license
 */
var fx = (function() {
var exports = {};

// src/OES_texture_float_linear-polyfill.js
// From: https://github.com/evanw/OES_texture_float_linear-polyfill
(function() {
  // Uploads a 2x2 floating-point texture where one pixel is 2 and the other
  // three pixels are 0. Linear filtering is only supported if a sample taken
  // from the center of that texture is (2 + 0 + 0 + 0) / 4 = 0.5.
  function supportsOESTextureFloatLinear(gl) {
    // Need floating point textures in the first place
    if (!gl.getExtension('OES_texture_float')) {
      return false;
    }

    // Create a render target
    var framebuffer = gl.createFramebuffer();
    var byteTexture = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, byteTexture);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, 1, 1, 0, gl.RGBA, gl.UNSIGNED_BYTE, null);
    gl.bindFramebuffer(gl.FRAMEBUFFER, framebuffer);
    gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, byteTexture, 0);

    // Create a simple floating-point texture with value of 0.5 in the center
    var rgba = [
      2, 0, 0, 0,
      0, 0, 0, 0,
      0, 0, 0, 0,
      0, 0, 0, 0
    ];
    var floatTexture = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, floatTexture);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, 2, 2, 0, gl.RGBA, gl.FLOAT, new Float32Array(rgba));

    // Create the test shader
    var program = gl.createProgram();
    var vertexShader = gl.createShader(gl.VERTEX_SHADER);
    var fragmentShader = gl.createShader(gl.FRAGMENT_SHADER);
    gl.shaderSource(vertexShader, '\
      attribute vec2 vertex;\
      void main() {\
        gl_Position = vec4(vertex, 0.0, 1.0);\
      }\
    ');
    gl.shaderSource(fragmentShader, '\
      uniform sampler2D texture;\
      void main() {\
        gl_FragColor = texture2D(texture, vec2(0.5));\
      }\
    ');
    gl.compileShader(vertexShader);
    gl.compileShader(fragmentShader);
    gl.attachShader(program, vertexShader);
    gl.attachShader(program, fragmentShader);
    gl.linkProgram(program);

    // Create a buffer containing a single point
    var buffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([0, 0]), gl.STREAM_DRAW);
    gl.enableVertexAttribArray(0);
    gl.vertexAttribPointer(0, 2, gl.FLOAT, false, 0, 0);

    // Render the point and read back the rendered pixel
    var pixel = new Uint8Array(4);
    gl.useProgram(program);
    gl.viewport(0, 0, 1, 1);
    gl.bindTexture(gl.TEXTURE_2D, floatTexture);
    gl.drawArrays(gl.POINTS, 0, 1);
    gl.readPixels(0, 0, 1, 1, gl.RGBA, gl.UNSIGNED_BYTE, pixel);

    // The center sample will only have a value of 0.5 if linear filtering works
    return pixel[0] === 127 || pixel[0] === 128;
  }

  // The constructor for the returned extension object
  function OESTextureFloatLinear() {
  }

  // Cache the extension so it's specific to each context like extensions should be
  function getOESTextureFloatLinear(gl) {
    if (gl.$OES_texture_float_linear$ === void 0) {
      Object.defineProperty(gl, '$OES_texture_float_linear$', {
        enumerable: false,
        configurable: false,
        writable: false,
        value: new OESTextureFloatLinear()
      });
    }
    return gl.$OES_texture_float_linear$;
  }

  // This replaces the real getExtension()
  function getExtension(name) {
    return name === 'OES_texture_float_linear'
      ? getOESTextureFloatLinear(this)
      : oldGetExtension.call(this, name);
  }

  // This replaces the real getSupportedExtensions()
  function getSupportedExtensions() {
    var extensions = oldGetSupportedExtensions.call(this);
    if (extensions.indexOf('OES_texture_float_linear') === -1) {
      extensions.push('OES_texture_float_linear');
    }
    return extensions;
  }

  // Get a WebGL context
  try {
    var gl = document.createElement('canvas').getContext('experimental-webgl');
  } catch (e) {
  }

  // Don't install the polyfill if the browser already supports it or doesn't have WebGL
  if (!gl || gl.getSupportedExtensions().indexOf('OES_texture_float_linear') !== -1) {
    return;
  }

  // Install the polyfill if linear filtering works with floating-point textures
  if (supportsOESTextureFloatLinear(gl)) {
    var oldGetExtension = WebGLRenderingContext.prototype.getExtension;
    var oldGetSupportedExtensions = WebGLRenderingContext.prototype.getSupportedExtensions;
    WebGLRenderingContext.prototype.getExtension = getExtension;
    WebGLRenderingContext.prototype.getSupportedExtensions = getSupportedExtensions;
  }
}());

// src/core/canvas.js
var gl;

function clamp(lo, value, hi) {
    return Math.max(lo, Math.min(value, hi));
}

function wrapTexture(texture) {
    return {
        _: texture,
        loadContentsOf: function(element) {
            // Make sure that we're using the correct global WebGL context
            gl = this._.gl;
            this._.loadContentsOf(element);
        },
        destroy: function() {
            // Make sure that we're using the correct global WebGL context
            gl = this._.gl;
            this._.destroy();
        }
    };
}

function texture(element) {
    return wrapTexture(Texture.fromElement(element));
}

function initialize(width, height) {
    var type = gl.UNSIGNED_BYTE;

    // Go for floating point buffer textures if we can, it'll make the bokeh
    // filter look a lot better. Note that on Windows, ANGLE does not let you
    // render to a floating-point texture when linear filtering is enabled.
    // See http://crbug.com/172278 for more information.
    if (gl.getExtension('OES_texture_float') && gl.getExtension('OES_texture_float_linear')) {
        var testTexture = new Texture(100, 100, gl.RGBA, gl.FLOAT);
        try {
            // Only use gl.FLOAT if we can render to it
            testTexture.drawTo(function() { type = gl.FLOAT; });
        } catch (e) {
        }
        testTexture.destroy();
    }

    if (this._.texture) this._.texture.destroy();
    if (this._.spareTexture) this._.spareTexture.destroy();
    this.width = width;
    this.height = height;
    this._.texture = new Texture(width, height, gl.RGBA, type);
    this._.spareTexture = new Texture(width, height, gl.RGBA, type);
    this._.extraTexture = this._.extraTexture || new Texture(0, 0, gl.RGBA, type);
    this._.flippedShader = this._.flippedShader || new Shader(null, '\
        uniform sampler2D texture;\
        varying vec2 texCoord;\
        void main() {\
            gl_FragColor = texture2D(texture, vec2(texCoord.x, 1.0 - texCoord.y));\
        }\
    ');
    this._.isInitialized = true;
}

/*
   Draw a texture to the canvas, with an optional width and height to scale to.
   If no width and height are given then the original texture width and height
   are used.
*/
function draw(texture, width, height) {
    if (!this._.isInitialized || texture._.width != this.width || texture._.height != this.height) {
        initialize.call(this, width ? width : texture._.width, height ? height : texture._.height);
    }

    texture._.use();
    this._.texture.drawTo(function() {
        Shader.getDefaultShader().drawRect();
    });

    return this;
}

function update() {
    this._.texture.use();
    this._.flippedShader.drawRect();
    return this;
}

function simpleShader(shader, uniforms, textureIn, textureOut) {
    (textureIn || this._.texture).use();
    this._.spareTexture.drawTo(function() {
        shader.uniforms(uniforms).drawRect();
    });
    this._.spareTexture.swapWith(textureOut || this._.texture);
}

function replace(node) {
    node.parentNode.insertBefore(this, node);
    node.parentNode.removeChild(node);
    return this;
}

function contents() {
    var texture = new Texture(this._.texture.width, this._.texture.height, gl.RGBA, gl.UNSIGNED_BYTE);
    this._.texture.use();
    texture.drawTo(function() {
        Shader.getDefaultShader().drawRect();
    });
    return wrapTexture(texture);
}

/*
   Get a Uint8 array of pixel values: [r, g, b, a, r, g, b, a, ...]
   Length of the array will be width * height * 4.
*/
function getPixelArray() {
    var w = this._.texture.width;
    var h = this._.texture.height;
    var array = new Uint8Array(w * h * 4);
    this._.texture.drawTo(function() {
        gl.readPixels(0, 0, w, h, gl.RGBA, gl.UNSIGNED_BYTE, array);
    });
    return array;
}

function wrap(func) {
    return function() {
        // Make sure that we're using the correct global WebGL context
        gl = this._.gl;

        // Now that the context has been switched, we can call the wrapped function
        return func.apply(this, arguments);
    };
}

exports.canvas = function() {
    var canvas = document.createElement('canvas');
    try {
        gl = canvas.getContext('experimental-webgl', { premultipliedAlpha: false });
    } catch (e) {
        gl = null;
    }
    if (!gl) {
        throw 'This browser does not support WebGL';
    }
    canvas._ = {
        gl: gl,
        isInitialized: false,
        texture: null,
        spareTexture: null,
        flippedShader: null
    };

    // Core methods
    canvas.texture = wrap(texture);
    canvas.draw = wrap(draw);
    canvas.update = wrap(update);
    canvas.replace = wrap(replace);
    canvas.contents = wrap(contents);
    canvas.getPixelArray = wrap(getPixelArray);

    // Filter methods
    canvas.brightnessContrast = wrap(brightnessContrast);
    canvas.hexagonalPixelate = wrap(hexagonalPixelate);
    canvas.hueSaturation = wrap(hueSaturation);
    canvas.colorHalftone = wrap(colorHalftone);
    canvas.triangleBlur = wrap(triangleBlur);
    canvas.unsharpMask = wrap(unsharpMask);
    canvas.perspective = wrap(perspective);
    canvas.matrixWarp = wrap(matrixWarp);
    canvas.bulgePinch = wrap(bulgePinch);
    canvas.tiltShift = wrap(tiltShift);
    canvas.dotScreen = wrap(dotScreen);
    canvas.edgeWork = wrap(edgeWork);
    canvas.lensBlur = wrap(lensBlur);
    canvas.zoomBlur = wrap(zoomBlur);
    canvas.noise = wrap(noise);
    canvas.denoise = wrap(denoise);
    canvas.curves = wrap(curves);
    canvas.swirl = wrap(swirl);
    canvas.ink = wrap(ink);
    canvas.vignette = wrap(vignette);
    canvas.vibrance = wrap(vibrance);
    canvas.sepia = wrap(sepia);

    return canvas;
};
exports.splineInterpolate = splineInterpolate;

// src/core/matrix.js
// from javax.media.jai.PerspectiveTransform

function getSquareToQuad(x0, y0, x1, y1, x2, y2, x3, y3) {
    var dx1 = x1 - x2;
    var dy1 = y1 - y2;
    var dx2 = x3 - x2;
    var dy2 = y3 - y2;
    var dx3 = x0 - x1 + x2 - x3;
    var dy3 = y0 - y1 + y2 - y3;
    var det = dx1*dy2 - dx2*dy1;
    var a = (dx3*dy2 - dx2*dy3) / det;
    var b = (dx1*dy3 - dx3*dy1) / det;
    return [
        x1 - x0 + a*x1, y1 - y0 + a*y1, a,
        x3 - x0 + b*x3, y3 - y0 + b*y3, b,
        x0, y0, 1
    ];
}

function getInverse(m) {
    var a = m[0], b = m[1], c = m[2];
    var d = m[3], e = m[4], f = m[5];
    var g = m[6], h = m[7], i = m[8];
    var det = a*e*i - a*f*h - b*d*i + b*f*g + c*d*h - c*e*g;
    return [
        (e*i - f*h) / det, (c*h - b*i) / det, (b*f - c*e) / det,
        (f*g - d*i) / det, (a*i - c*g) / det, (c*d - a*f) / det,
        (d*h - e*g) / det, (b*g - a*h) / det, (a*e - b*d) / det
    ];
}

function multiply(a, b) {
    return [
        a[0]*b[0] + a[1]*b[3] + a[2]*b[6],
        a[0]*b[1] + a[1]*b[4] + a[2]*b[7],
        a[0]*b[2] + a[1]*b[5] + a[2]*b[8],
        a[3]*b[0] + a[4]*b[3] + a[5]*b[6],
        a[3]*b[1] + a[4]*b[4] + a[5]*b[7],
        a[3]*b[2] + a[4]*b[5] + a[5]*b[8],
        a[6]*b[0] + a[7]*b[3] + a[8]*b[6],
        a[6]*b[1] + a[7]*b[4] + a[8]*b[7],
        a[6]*b[2] + a[7]*b[5] + a[8]*b[8]
    ];
}

// src/core/shader.js
var Shader = (function() {
    function isArray(obj) {
        return Object.prototype.toString.call(obj) == '[object Array]';
    }

    function isNumber(obj) {
        return Object.prototype.toString.call(obj) == '[object Number]';
    }

    function compileSource(type, source) {
        var shader = gl.createShader(type);
        gl.shaderSource(shader, source);
        gl.compileShader(shader);
        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            throw 'compile error: ' + gl.getShaderInfoLog(shader);
        }
        return shader;
    }

    var defaultVertexSource = '\
    attribute vec2 vertex;\
    attribute vec2 _texCoord;\
    varying vec2 texCoord;\
    void main() {\
        texCoord = _texCoord;\
        gl_Position = vec4(vertex * 2.0 - 1.0, 0.0, 1.0);\
    }';

    var defaultFragmentSource = '\
    uniform sampler2D texture;\
    varying vec2 texCoord;\
    void main() {\
        gl_FragColor = texture2D(texture, texCoord);\
    }';

    function Shader(vertexSource, fragmentSource) {
        this.vertexAttribute = null;
        this.texCoordAttribute = null;
        this.program = gl.createProgram();
        vertexSource = vertexSource || defaultVertexSource;
        fragmentSource = fragmentSource || defaultFragmentSource;
        fragmentSource = 'precision highp float;' + fragmentSource; // annoying requirement is annoying
        gl.attachShader(this.program, compileSource(gl.VERTEX_SHADER, vertexSource));
        gl.attachShader(this.program, compileSource(gl.FRAGMENT_SHADER, fragmentSource));
        gl.linkProgram(this.program);
        if (!gl.getProgramParameter(this.program, gl.LINK_STATUS)) {
            throw 'link error: ' + gl.getProgramInfoLog(this.program);
        }
    }

    Shader.prototype.destroy = function() {
        gl.deleteProgram(this.program);
        this.program = null;
    };

    Shader.prototype.uniforms = function(uniforms) {
        gl.useProgram(this.program);
        for (var name in uniforms) {
            if (!uniforms.hasOwnProperty(name)) continue;
            var location = gl.getUniformLocation(this.program, name);
            if (location === null) continue; // will be null if the uniform isn't used in the shader
            var value = uniforms[name];
            if (isArray(value)) {
                switch (value.length) {
                    case 1: gl.uniform1fv(location, new Float32Array(value)); break;
                    case 2: gl.uniform2fv(location, new Float32Array(value)); break;
                    case 3: gl.uniform3fv(location, new Float32Array(value)); break;
                    case 4: gl.uniform4fv(location, new Float32Array(value)); break;
                    case 9: gl.uniformMatrix3fv(location, false, new Float32Array(value)); break;
                    case 16: gl.uniformMatrix4fv(location, false, new Float32Array(value)); break;
                    default: throw 'dont\'t know how to load uniform "' + name + '" of length ' + value.length;
                }
            } else if (isNumber(value)) {
                gl.uniform1f(location, value);
            } else {
                throw 'attempted to set uniform "' + name + '" to invalid value ' + (value || 'undefined').toString();
            }
        }
        // allow chaining
        return this;
    };

    // textures are uniforms too but for some reason can't be specified by gl.uniform1f,
    // even though floating point numbers represent the integers 0 through 7 exactly
    Shader.prototype.textures = function(textures) {
        gl.useProgram(this.program);
        for (var name in textures) {
            if (!textures.hasOwnProperty(name)) continue;
            gl.uniform1i(gl.getUniformLocation(this.program, name), textures[name]);
        }
        // allow chaining
        return this;
    };

    Shader.prototype.drawRect = function(left, top, right, bottom) {
        var undefined;
        var viewport = gl.getParameter(gl.VIEWPORT);
        top = top !== undefined ? (top - viewport[1]) / viewport[3] : 0;
        left = left !== undefined ? (left - viewport[0]) / viewport[2] : 0;
        right = right !== undefined ? (right - viewport[0]) / viewport[2] : 1;
        bottom = bottom !== undefined ? (bottom - viewport[1]) / viewport[3] : 1;
        if (gl.vertexBuffer == null) {
            gl.vertexBuffer = gl.createBuffer();
        }
        gl.bindBuffer(gl.ARRAY_BUFFER, gl.vertexBuffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([ left, top, left, bottom, right, top, right, bottom ]), gl.STATIC_DRAW);
        if (gl.texCoordBuffer == null) {
            gl.texCoordBuffer = gl.createBuffer();
            gl.bindBuffer(gl.ARRAY_BUFFER, gl.texCoordBuffer);
            gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([ 0, 0, 0, 1, 1, 0, 1, 1 ]), gl.STATIC_DRAW);
        }
        if (this.vertexAttribute == null) {
            this.vertexAttribute = gl.getAttribLocation(this.program, 'vertex');
            gl.enableVertexAttribArray(this.vertexAttribute);
        }
        if (this.texCoordAttribute == null) {
            this.texCoordAttribute = gl.getAttribLocation(this.program, '_texCoord');
            gl.enableVertexAttribArray(this.texCoordAttribute);
        }
        gl.useProgram(this.program);
        gl.bindBuffer(gl.ARRAY_BUFFER, gl.vertexBuffer);
        gl.vertexAttribPointer(this.vertexAttribute, 2, gl.FLOAT, false, 0, 0);
        gl.bindBuffer(gl.ARRAY_BUFFER, gl.texCoordBuffer);
        gl.vertexAttribPointer(this.texCoordAttribute, 2, gl.FLOAT, false, 0, 0);
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
    };

    Shader.getDefaultShader = function() {
        gl.defaultShader = gl.defaultShader || new Shader();
        return gl.defaultShader;
    };

    return Shader;
})();

// src/core/spline.js
// from SplineInterpolator.cs in the Paint.NET source code

function SplineInterpolator(points) {
    var n = points.length;
    this.xa = [];
    this.ya = [];
    this.u = [];
    this.y2 = [];

    points.sort(function(a, b) {
        return a[0] - b[0];
    });
    for (var i = 0; i < n; i++) {
        this.xa.push(points[i][0]);
        this.ya.push(points[i][1]);
    }

    this.u[0] = 0;
    this.y2[0] = 0;

    for (var i = 1; i < n - 1; ++i) {
        // This is the decomposition loop of the tridiagonal algorithm. 
        // y2 and u are used for temporary storage of the decomposed factors.
        var wx = this.xa[i + 1] - this.xa[i - 1];
        var sig = (this.xa[i] - this.xa[i - 1]) / wx;
        var p = sig * this.y2[i - 1] + 2.0;

        this.y2[i] = (sig - 1.0) / p;

        var ddydx = 
            (this.ya[i + 1] - this.ya[i]) / (this.xa[i + 1] - this.xa[i]) - 
            (this.ya[i] - this.ya[i - 1]) / (this.xa[i] - this.xa[i - 1]);

        this.u[i] = (6.0 * ddydx / wx - sig * this.u[i - 1]) / p;
    }

    this.y2[n - 1] = 0;

    // This is the backsubstitution loop of the tridiagonal algorithm
    for (var i = n - 2; i >= 0; --i) {
        this.y2[i] = this.y2[i] * this.y2[i + 1] + this.u[i];
    }
}

SplineInterpolator.prototype.interpolate = function(x) {
    var n = this.ya.length;
    var klo = 0;
    var khi = n - 1;

    // We will find the right place in the table by means of
    // bisection. This is optimal if sequential calls to this
    // routine are at random values of x. If sequential calls
    // are in order, and closely spaced, one would do better
    // to store previous values of klo and khi.
    while (khi - klo > 1) {
        var k = (khi + klo) >> 1;

        if (this.xa[k] > x) {
            khi = k; 
        } else {
            klo = k;
        }
    }

    var h = this.xa[khi] - this.xa[klo];
    var a = (this.xa[khi] - x) / h;
    var b = (x - this.xa[klo]) / h;

    // Cubic spline polynomial is now evaluated.
    return a * this.ya[klo] + b * this.ya[khi] + 
        ((a * a * a - a) * this.y2[klo] + (b * b * b - b) * this.y2[khi]) * (h * h) / 6.0;
};

// src/core/texture.js
var Texture = (function() {
    Texture.fromElement = function(element) {
        var texture = new Texture(0, 0, gl.RGBA, gl.UNSIGNED_BYTE);
        texture.loadContentsOf(element);
        return texture;
    };

    function Texture(width, height, format, type) {
        this.gl = gl;
        this.id = gl.createTexture();
        this.width = width;
        this.height = height;
        this.format = format;
        this.type = type;

        gl.bindTexture(gl.TEXTURE_2D, this.id);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
        if (width && height) gl.texImage2D(gl.TEXTURE_2D, 0, this.format, width, height, 0, this.format, this.type, null);
    }

    Texture.prototype.loadContentsOf = function(element) {
        this.width = element.width || element.videoWidth;
        this.height = element.height || element.videoHeight;
        gl.bindTexture(gl.TEXTURE_2D, this.id);
        gl.texImage2D(gl.TEXTURE_2D, 0, this.format, this.format, this.type, element);
    };

    Texture.prototype.initFromBytes = function(width, height, data) {
        this.width = width;
        this.height = height;
        this.format = gl.RGBA;
        this.type = gl.UNSIGNED_BYTE;
        gl.bindTexture(gl.TEXTURE_2D, this.id);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, width, height, 0, gl.RGBA, this.type, new Uint8Array(data));
    };

    Texture.prototype.destroy = function() {
        gl.deleteTexture(this.id);
        this.id = null;
    };

    Texture.prototype.use = function(unit) {
        gl.activeTexture(gl.TEXTURE0 + (unit || 0));
        gl.bindTexture(gl.TEXTURE_2D, this.id);
    };

    Texture.prototype.unuse = function(unit) {
        gl.activeTexture(gl.TEXTURE0 + (unit || 0));
        gl.bindTexture(gl.TEXTURE_2D, null);
    };

    Texture.prototype.ensureFormat = function(width, height, format, type) {
        // allow passing an existing texture instead of individual arguments
        if (arguments.length == 1) {
            var texture = arguments[0];
            width = texture.width;
            height = texture.height;
            format = texture.format;
            type = texture.type;
        }

        // change the format only if required
        if (width != this.width || height != this.height || format != this.format || type != this.type) {
            this.width = width;
            this.height = height;
            this.format = format;
            this.type = type;
            gl.bindTexture(gl.TEXTURE_2D, this.id);
            gl.texImage2D(gl.TEXTURE_2D, 0, this.format, width, height, 0, this.format, this.type, null);
        }
    };

    Texture.prototype.drawTo = function(callback) {
        // start rendering to this texture
        gl.framebuffer = gl.framebuffer || gl.createFramebuffer();
        gl.bindFramebuffer(gl.FRAMEBUFFER, gl.framebuffer);
        gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, this.id, 0);
        if (gl.checkFramebufferStatus(gl.FRAMEBUFFER) !== gl.FRAMEBUFFER_COMPLETE) {
            throw new Error('incomplete framebuffer');
        }
        gl.viewport(0, 0, this.width, this.height);

        // do the drawing
        callback();

        // stop rendering to this texture
        gl.bindFramebuffer(gl.FRAMEBUFFER, null);
    };

    var canvas = null;

    function getCanvas(texture) {
        if (canvas == null) canvas = document.createElement('canvas');
        canvas.width = texture.width;
        canvas.height = texture.height;
        var c = canvas.getContext('2d');
        c.clearRect(0, 0, canvas.width, canvas.height);
        return c;
    }

    Texture.prototype.fillUsingCanvas = function(callback) {
        callback(getCanvas(this));
        this.format = gl.RGBA;
        this.type = gl.UNSIGNED_BYTE;
        gl.bindTexture(gl.TEXTURE_2D, this.id);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, canvas);
        return this;
    };

    Texture.prototype.toImage = function(image) {
        this.use();
        Shader.getDefaultShader().drawRect();
        var size = this.width * this.height * 4;
        var pixels = new Uint8Array(size);
        var c = getCanvas(this);
        var data = c.createImageData(this.width, this.height);
        gl.readPixels(0, 0, this.width, this.height, gl.RGBA, gl.UNSIGNED_BYTE, pixels);
        for (var i = 0; i < size; i++) {
            data.data[i] = pixels[i];
        }
        c.putImageData(data, 0, 0);
        image.src = canvas.toDataURL();
    };

    Texture.prototype.swapWith = function(other) {
        var temp;
        temp = other.id; other.id = this.id; this.id = temp;
        temp = other.width; other.width = this.width; this.width = temp;
        temp = other.height; other.height = this.height; this.height = temp;
        temp = other.format; other.format = this.format; this.format = temp;
    };

    return Texture;
})();

// src/filters/common.js
function warpShader(uniforms, warp) {
    return new Shader(null, uniforms + '\
    uniform sampler2D texture;\
    uniform vec2 texSize;\
    varying vec2 texCoord;\
    void main() {\
        vec2 coord = texCoord * texSize;\
        ' + warp + '\
        gl_FragColor = texture2D(texture, coord / texSize);\
        vec2 clampedCoord = clamp(coord, vec2(0.0), texSize);\
        if (coord != clampedCoord) {\
            /* fade to transparent if we are outside the image */\
            gl_FragColor.a *= max(0.0, 1.0 - length(coord - clampedCoord));\
        }\
    }');
}

// returns a random number between 0 and 1
var randomShaderFunc = '\
    float random(vec3 scale, float seed) {\
        /* use the fragment position for a different seed per-pixel */\
        return fract(sin(dot(gl_FragCoord.xyz + seed, scale)) * 43758.5453 + seed);\
    }\
';

// src/filters/adjust/brightnesscontrast.js
/**
 * @filter           Brightness / Contrast
 * @description      Provides additive brightness and multiplicative contrast control.
 * @param brightness -1 to 1 (-1 is solid black, 0 is no change, and 1 is solid white)
 * @param contrast   -1 to 1 (-1 is solid gray, 0 is no change, and 1 is maximum contrast)
 */
function brightnessContrast(brightness, contrast) {
    gl.brightnessContrast = gl.brightnessContrast || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float brightness;\
        uniform float contrast;\
        varying vec2 texCoord;\
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            color.rgb += brightness;\
            if (contrast > 0.0) {\
                color.rgb = (color.rgb - 0.5) / (1.0 - contrast) + 0.5;\
            } else {\
                color.rgb = (color.rgb - 0.5) * (1.0 + contrast) + 0.5;\
            }\
            gl_FragColor = color;\
        }\
    ');

    simpleShader.call(this, gl.brightnessContrast, {
        brightness: clamp(-1, brightness, 1),
        contrast: clamp(-1, contrast, 1)
    });

    return this;
}

// src/filters/adjust/curves.js
function splineInterpolate(points) {
    var interpolator = new SplineInterpolator(points);
    var array = [];
    for (var i = 0; i < 256; i++) {
        array.push(clamp(0, Math.floor(interpolator.interpolate(i / 255) * 256), 255));
    }
    return array;
}

/**
 * @filter      Curves
 * @description A powerful mapping tool that transforms the colors in the image
 *              by an arbitrary function. The function is interpolated between
 *              a set of 2D points using splines. The curves filter can take
 *              either one or three arguments which will apply the mapping to
 *              either luminance or RGB values, respectively.
 * @param red   A list of points that define the function for the red channel.
 *              Each point is a list of two values: the value before the mapping
 *              and the value after the mapping, both in the range 0 to 1. For
 *              example, [[0,1], [1,0]] would invert the red channel while
 *              [[0,0], [1,1]] would leave the red channel unchanged. If green
 *              and blue are omitted then this argument also applies to the
 *              green and blue channels.
 * @param green (optional) A list of points that define the function for the green
 *              channel (just like for red).
 * @param blue  (optional) A list of points that define the function for the blue
 *              channel (just like for red).
 */
function curves(red, green, blue) {
    // Create the ramp texture
    red = splineInterpolate(red);
    if (arguments.length == 1) {
        green = blue = red;
    } else {
        green = splineInterpolate(green);
        blue = splineInterpolate(blue);
    }
    var array = [];
    for (var i = 0; i < 256; i++) {
        array.splice(array.length, 0, red[i], green[i], blue[i], 255);
    }
    this._.extraTexture.initFromBytes(256, 1, array);
    this._.extraTexture.use(1);

    gl.curves = gl.curves || new Shader(null, '\
        uniform sampler2D texture;\
        uniform sampler2D map;\
        varying vec2 texCoord;\
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            color.r = texture2D(map, vec2(color.r)).r;\
            color.g = texture2D(map, vec2(color.g)).g;\
            color.b = texture2D(map, vec2(color.b)).b;\
            gl_FragColor = color;\
        }\
    ');

    gl.curves.textures({
        map: 1
    });
    simpleShader.call(this, gl.curves, {});

    return this;
}

// src/filters/adjust/denoise.js
/**
 * @filter         Denoise
 * @description    Smooths over grainy noise in dark images using an 9x9 box filter
 *                 weighted by color intensity, similar to a bilateral filter.
 * @param exponent The exponent of the color intensity difference, should be greater
 *                 than zero. A value of zero just gives an 9x9 box blur and high values
 *                 give the original image, but ideal values are usually around 10-20.
 */
function denoise(exponent) {
    // Do a 9x9 bilateral box filter
    gl.denoise = gl.denoise || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float exponent;\
        uniform float strength;\
        uniform vec2 texSize;\
        varying vec2 texCoord;\
        void main() {\
            vec4 center = texture2D(texture, texCoord);\
            vec4 color = vec4(0.0);\
            float total = 0.0;\
            for (float x = -4.0; x <= 4.0; x += 1.0) {\
                for (float y = -4.0; y <= 4.0; y += 1.0) {\
                    vec4 sample = texture2D(texture, texCoord + vec2(x, y) / texSize);\
                    float weight = 1.0 - abs(dot(sample.rgb - center.rgb, vec3(0.25)));\
                    weight = pow(weight, exponent);\
                    color += sample * weight;\
                    total += weight;\
                }\
            }\
            gl_FragColor = color / total;\
        }\
    ');

    // Perform two iterations for stronger results
    for (var i = 0; i < 2; i++) {
        simpleShader.call(this, gl.denoise, {
            exponent: Math.max(0, exponent),
            texSize: [this.width, this.height]
        });
    }

    return this;
}

// src/filters/adjust/huesaturation.js
/**
 * @filter           Hue / Saturation
 * @description      Provides rotational hue and multiplicative saturation control. RGB color space
 *                   can be imagined as a cube where the axes are the red, green, and blue color
 *                   values. Hue changing works by rotating the color vector around the grayscale
 *                   line, which is the straight line from black (0, 0, 0) to white (1, 1, 1).
 *                   Saturation is implemented by scaling all color channel values either toward
 *                   or away from the average color channel value.
 * @param hue        -1 to 1 (-1 is 180 degree rotation in the negative direction, 0 is no change,
 *                   and 1 is 180 degree rotation in the positive direction)
 * @param saturation -1 to 1 (-1 is solid gray, 0 is no change, and 1 is maximum contrast)
 */
function hueSaturation(hue, saturation) {
    gl.hueSaturation = gl.hueSaturation || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float hue;\
        uniform float saturation;\
        varying vec2 texCoord;\
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            \
            /* hue adjustment, wolfram alpha: RotationTransform[angle, {1, 1, 1}][{x, y, z}] */\
            float angle = hue * 3.14159265;\
            float s = sin(angle), c = cos(angle);\
            vec3 weights = (vec3(2.0 * c, -sqrt(3.0) * s - c, sqrt(3.0) * s - c) + 1.0) / 3.0;\
            float len = length(color.rgb);\
            color.rgb = vec3(\
                dot(color.rgb, weights.xyz),\
                dot(color.rgb, weights.zxy),\
                dot(color.rgb, weights.yzx)\
            );\
            \
            /* saturation adjustment */\
            float average = (color.r + color.g + color.b) / 3.0;\
            if (saturation > 0.0) {\
                color.rgb += (average - color.rgb) * (1.0 - 1.0 / (1.001 - saturation));\
            } else {\
                color.rgb += (average - color.rgb) * (-saturation);\
            }\
            \
            gl_FragColor = color;\
        }\
    ');

    simpleShader.call(this, gl.hueSaturation, {
        hue: clamp(-1, hue, 1),
        saturation: clamp(-1, saturation, 1)
    });

    return this;
}

// src/filters/adjust/noise.js
/**
 * @filter         Noise
 * @description    Adds black and white noise to the image.
 * @param amount   0 to 1 (0 for no effect, 1 for maximum noise)
 */
function noise(amount) {
    gl.noise = gl.noise || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float amount;\
        varying vec2 texCoord;\
        float rand(vec2 co) {\
            return fract(sin(dot(co.xy ,vec2(12.9898,78.233))) * 43758.5453);\
        }\
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            \
            float diff = (rand(texCoord) - 0.5) * amount;\
            color.r += diff;\
            color.g += diff;\
            color.b += diff;\
            \
            gl_FragColor = color;\
        }\
    ');

    simpleShader.call(this, gl.noise, {
        amount: clamp(0, amount, 1)
    });

    return this;
}

// src/filters/adjust/sepia.js
/**
 * @filter         Sepia
 * @description    Gives the image a reddish-brown monochrome tint that imitates an old photograph.
 * @param amount   0 to 1 (0 for no effect, 1 for full sepia coloring)
 */
function sepia(amount) {
    gl.sepia = gl.sepia || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float amount;\
        varying vec2 texCoord;\
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            float r = color.r;\
            float g = color.g;\
            float b = color.b;\
            \
            color.r = min(1.0, (r * (1.0 - (0.607 * amount))) + (g * (0.769 * amount)) + (b * (0.189 * amount)));\
            color.g = min(1.0, (r * 0.349 * amount) + (g * (1.0 - (0.314 * amount))) + (b * 0.168 * amount));\
            color.b = min(1.0, (r * 0.272 * amount) + (g * 0.534 * amount) + (b * (1.0 - (0.869 * amount))));\
            \
            gl_FragColor = color;\
        }\
    ');

    simpleShader.call(this, gl.sepia, {
        amount: clamp(0, amount, 1)
    });

    return this;
}

// src/filters/adjust/unsharpmask.js
/**
 * @filter         Unsharp Mask
 * @description    A form of image sharpening that amplifies high-frequencies in the image. It
 *                 is implemented by scaling pixels away from the average of their neighbors.
 * @param radius   The blur radius that calculates the average of the neighboring pixels.
 * @param strength A scale factor where 0 is no effect and higher values cause a stronger effect.
 */
function unsharpMask(radius, strength) {
    gl.unsharpMask = gl.unsharpMask || new Shader(null, '\
        uniform sampler2D blurredTexture;\
        uniform sampler2D originalTexture;\
        uniform float strength;\
        uniform float threshold;\
        varying vec2 texCoord;\
        void main() {\
            vec4 blurred = texture2D(blurredTexture, texCoord);\
            vec4 original = texture2D(originalTexture, texCoord);\
            gl_FragColor = mix(blurred, original, 1.0 + strength);\
        }\
    ');

    // Store a copy of the current texture in the second texture unit
    this._.extraTexture.ensureFormat(this._.texture);
    this._.texture.use();
    this._.extraTexture.drawTo(function() {
        Shader.getDefaultShader().drawRect();
    });

    // Blur the current texture, then use the stored texture to detect edges
    this._.extraTexture.use(1);
    this.triangleBlur(radius);
    gl.unsharpMask.textures({
        originalTexture: 1
    });
    simpleShader.call(this, gl.unsharpMask, {
        strength: strength
    });
    this._.extraTexture.unuse(1);

    return this;
}

// src/filters/adjust/vibrance.js
/**
 * @filter       Vibrance
 * @description  Modifies the saturation of desaturated colors, leaving saturated colors unmodified.
 * @param amount -1 to 1 (-1 is minimum vibrance, 0 is no change, and 1 is maximum vibrance)
 */
function vibrance(amount) {
    gl.vibrance = gl.vibrance || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float amount;\
        varying vec2 texCoord;\
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            float average = (color.r + color.g + color.b) / 3.0;\
            float mx = max(color.r, max(color.g, color.b));\
            float amt = (mx - average) * (-amount * 3.0);\
            color.rgb = mix(color.rgb, vec3(mx), amt);\
            gl_FragColor = color;\
        }\
    ');

    simpleShader.call(this, gl.vibrance, {
        amount: clamp(-1, amount, 1)
    });

    return this;
}

// src/filters/adjust/vignette.js
/**
 * @filter         Vignette
 * @description    Adds a simulated lens edge darkening effect.
 * @param size     0 to 1 (0 for center of frame, 1 for edge of frame)
 * @param amount   0 to 1 (0 for no effect, 1 for maximum lens darkening)
 */
function vignette(size, amount) {
    gl.vignette = gl.vignette || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float size;\
        uniform float amount;\
        varying vec2 texCoord;\
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            \
            float dist = distance(texCoord, vec2(0.5, 0.5));\
            color.rgb *= smoothstep(0.8, size * 0.799, dist * (amount + size));\
            \
            gl_FragColor = color;\
        }\
    ');

    simpleShader.call(this, gl.vignette, {
        size: clamp(0, size, 1),
        amount: clamp(0, amount, 1)
    });

    return this;
}

// src/filters/blur/lensblur.js
/**
 * @filter           Lens Blur
 * @description      Imitates a camera capturing the image out of focus by using a blur that generates
 *                   the large shapes known as bokeh. The polygonal shape of real bokeh is due to the
 *                   blades of the aperture diaphragm when it isn't fully open. This blur renders
 *                   bokeh from a 6-bladed diaphragm because the computation is more efficient. It
 *                   can be separated into three rhombi, each of which is just a skewed box blur.
 *                   This filter makes use of the floating point texture WebGL extension to implement
 *                   the brightness parameter, so there will be severe visual artifacts if brightness
 *                   is non-zero and the floating point texture extension is not available. The
 *                   idea was from John White's SIGGRAPH 2011 talk but this effect has an additional
 *                   brightness parameter that fakes what would otherwise come from a HDR source.
 * @param radius     the radius of the hexagonal disk convolved with the image
 * @param brightness -1 to 1 (the brightness of the bokeh, negative values will create dark bokeh)
 * @param angle      the rotation of the bokeh in radians
 */
function lensBlur(radius, brightness, angle) {
    // All averaging is done on values raised to a power to make more obvious bokeh
    // (we will raise the average to the inverse power at the end to compensate).
    // Without this the image looks almost like a normal blurred image. This hack is
    // obviously not realistic, but to accurately simulate this we would need a high
    // dynamic range source photograph which we don't have.
    gl.lensBlurPrePass = gl.lensBlurPrePass || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float power;\
        varying vec2 texCoord;\
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            color = pow(color, vec4(power));\
            gl_FragColor = vec4(color);\
        }\
    ');

    var common = '\
        uniform sampler2D texture0;\
        uniform sampler2D texture1;\
        uniform vec2 delta0;\
        uniform vec2 delta1;\
        uniform float power;\
        varying vec2 texCoord;\
        ' + randomShaderFunc + '\
        vec4 sample(vec2 delta) {\
            /* randomize the lookup values to hide the fixed number of samples */\
            float offset = random(vec3(delta, 151.7182), 0.0);\
            \
            vec4 color = vec4(0.0);\
            float total = 0.0;\
            for (float t = 0.0; t <= 30.0; t++) {\
                float percent = (t + offset) / 30.0;\
                color += texture2D(texture0, texCoord + delta * percent);\
                total += 1.0;\
            }\
            return color / total;\
        }\
    ';

    gl.lensBlur0 = gl.lensBlur0 || new Shader(null, common + '\
        void main() {\
            gl_FragColor = sample(delta0);\
        }\
    ');
    gl.lensBlur1 = gl.lensBlur1 || new Shader(null, common + '\
        void main() {\
            gl_FragColor = (sample(delta0) + sample(delta1)) * 0.5;\
        }\
    ');
    gl.lensBlur2 = gl.lensBlur2 || new Shader(null, common + '\
        void main() {\
            vec4 color = (sample(delta0) + 2.0 * texture2D(texture1, texCoord)) / 3.0;\
            gl_FragColor = pow(color, vec4(power));\
        }\
    ').textures({ texture1: 1 });

    // Generate
    var dir = [];
    for (var i = 0; i < 3; i++) {
        var a = angle + i * Math.PI * 2 / 3;
        dir.push([radius * Math.sin(a) / this.width, radius * Math.cos(a) / this.height]);
    }
    var power = Math.pow(10, clamp(-1, brightness, 1));

    // Remap the texture values, which will help make the bokeh effect
    simpleShader.call(this, gl.lensBlurPrePass, {
        power: power
    });

    // Blur two rhombi in parallel into extraTexture
    this._.extraTexture.ensureFormat(this._.texture);
    simpleShader.call(this, gl.lensBlur0, {
        delta0: dir[0]
    }, this._.texture, this._.extraTexture);
    simpleShader.call(this, gl.lensBlur1, {
        delta0: dir[1],
        delta1: dir[2]
    }, this._.extraTexture, this._.extraTexture);

    // Blur the last rhombus and combine with extraTexture
    simpleShader.call(this, gl.lensBlur0, {
        delta0: dir[1]
    });
    this._.extraTexture.use(1);
    simpleShader.call(this, gl.lensBlur2, {
        power: 1 / power,
        delta0: dir[2]
    });

    return this;
}

// src/filters/blur/tiltshift.js
/**
 * @filter               Tilt Shift
 * @description          Simulates the shallow depth of field normally encountered in close-up
 *                       photography, which makes the scene seem much smaller than it actually
 *                       is. This filter assumes the scene is relatively planar, in which case
 *                       the part of the scene that is completely in focus can be described by
 *                       a line (the intersection of the focal plane and the scene). An example
 *                       of a planar scene might be looking at a road from above at a downward
 *                       angle. The image is then blurred with a blur radius that starts at zero
 *                       on the line and increases further from the line.
 * @param startX         The x coordinate of the start of the line segment.
 * @param startY         The y coordinate of the start of the line segment.
 * @param endX           The x coordinate of the end of the line segment.
 * @param endY           The y coordinate of the end of the line segment.
 * @param blurRadius     The maximum radius of the pyramid blur.
 * @param gradientRadius The distance from the line at which the maximum blur radius is reached.
 */
function tiltShift(startX, startY, endX, endY, blurRadius, gradientRadius) {
    gl.tiltShift = gl.tiltShift || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float blurRadius;\
        uniform float gradientRadius;\
        uniform vec2 start;\
        uniform vec2 end;\
        uniform vec2 delta;\
        uniform vec2 texSize;\
        varying vec2 texCoord;\
        ' + randomShaderFunc + '\
        void main() {\
            vec4 color = vec4(0.0);\
            float total = 0.0;\
            \
            /* randomize the lookup values to hide the fixed number of samples */\
            float offset = random(vec3(12.9898, 78.233, 151.7182), 0.0);\
            \
            vec2 normal = normalize(vec2(start.y - end.y, end.x - start.x));\
            float radius = smoothstep(0.0, 1.0, abs(dot(texCoord * texSize - start, normal)) / gradientRadius) * blurRadius;\
            for (float t = -30.0; t <= 30.0; t++) {\
                float percent = (t + offset - 0.5) / 30.0;\
                float weight = 1.0 - abs(percent);\
                vec4 sample = texture2D(texture, texCoord + delta / texSize * percent * radius);\
                \
                /* switch to pre-multiplied alpha to correctly blur transparent images */\
                sample.rgb *= sample.a;\
                \
                color += sample * weight;\
                total += weight;\
            }\
            \
            gl_FragColor = color / total;\
            \
            /* switch back from pre-multiplied alpha */\
            gl_FragColor.rgb /= gl_FragColor.a + 0.00001;\
        }\
    ');

    var dx = endX - startX;
    var dy = endY - startY;
    var d = Math.sqrt(dx * dx + dy * dy);
    simpleShader.call(this, gl.tiltShift, {
        blurRadius: blurRadius,
        gradientRadius: gradientRadius,
        start: [startX, startY],
        end: [endX, endY],
        delta: [dx / d, dy / d],
        texSize: [this.width, this.height]
    });
    simpleShader.call(this, gl.tiltShift, {
        blurRadius: blurRadius,
        gradientRadius: gradientRadius,
        start: [startX, startY],
        end: [endX, endY],
        delta: [-dy / d, dx / d],
        texSize: [this.width, this.height]
    });

    return this;
}

// src/filters/blur/triangleblur.js
/**
 * @filter       Triangle Blur
 * @description  This is the most basic blur filter, which convolves the image with a
 *               pyramid filter. The pyramid filter is separable and is applied as two
 *               perpendicular triangle filters.
 * @param radius The radius of the pyramid convolved with the image.
 */
function triangleBlur(radius) {
    gl.triangleBlur = gl.triangleBlur || new Shader(null, '\
        uniform sampler2D texture;\
        uniform vec2 delta;\
        varying vec2 texCoord;\
        ' + randomShaderFunc + '\
        void main() {\
            vec4 color = vec4(0.0);\
            float total = 0.0;\
            \
            /* randomize the lookup values to hide the fixed number of samples */\
            float offset = random(vec3(12.9898, 78.233, 151.7182), 0.0);\
            \
            for (float t = -30.0; t <= 30.0; t++) {\
                float percent = (t + offset - 0.5) / 30.0;\
                float weight = 1.0 - abs(percent);\
                vec4 sample = texture2D(texture, texCoord + delta * percent);\
                \
                /* switch to pre-multiplied alpha to correctly blur transparent images */\
                sample.rgb *= sample.a;\
                \
                color += sample * weight;\
                total += weight;\
            }\
            \
            gl_FragColor = color / total;\
            \
            /* switch back from pre-multiplied alpha */\
            gl_FragColor.rgb /= gl_FragColor.a + 0.00001;\
        }\
    ');

    simpleShader.call(this, gl.triangleBlur, {
        delta: [radius / this.width, 0]
    });
    simpleShader.call(this, gl.triangleBlur, {
        delta: [0, radius / this.height]
    });

    return this;
}

// src/filters/blur/zoomblur.js
/**
 * @filter         Zoom Blur
 * @description    Blurs the image away from a certain point, which looks like radial motion blur.
 * @param centerX  The x coordinate of the blur origin.
 * @param centerY  The y coordinate of the blur origin.
 * @param strength The strength of the blur. Values in the range 0 to 1 are usually sufficient,
 *                 where 0 doesn't change the image and 1 creates a highly blurred image.
 */
function zoomBlur(centerX, centerY, strength) {
    gl.zoomBlur = gl.zoomBlur || new Shader(null, '\
        uniform sampler2D texture;\
        uniform vec2 center;\
        uniform float strength;\
        uniform vec2 texSize;\
        varying vec2 texCoord;\
        ' + randomShaderFunc + '\
        void main() {\
            vec4 color = vec4(0.0);\
            float total = 0.0;\
            vec2 toCenter = center - texCoord * texSize;\
            \
            /* randomize the lookup values to hide the fixed number of samples */\
            float offset = random(vec3(12.9898, 78.233, 151.7182), 0.0);\
            \
            for (float t = 0.0; t <= 40.0; t++) {\
                float percent = (t + offset) / 40.0;\
                float weight = 4.0 * (percent - percent * percent);\
                vec4 sample = texture2D(texture, texCoord + toCenter * percent * strength / texSize);\
                \
                /* switch to pre-multiplied alpha to correctly blur transparent images */\
                sample.rgb *= sample.a;\
                \
                color += sample * weight;\
                total += weight;\
            }\
            \
            gl_FragColor = color / total;\
            \
            /* switch back from pre-multiplied alpha */\
            gl_FragColor.rgb /= gl_FragColor.a + 0.00001;\
        }\
    ');

    simpleShader.call(this, gl.zoomBlur, {
        center: [centerX, centerY],
        strength: strength,
        texSize: [this.width, this.height]
    });

    return this;
}

// src/filters/fun/colorhalftone.js
/**
 * @filter        Color Halftone
 * @description   Simulates a CMYK halftone rendering of the image by multiplying pixel values
 *                with a four rotated 2D sine wave patterns, one each for cyan, magenta, yellow,
 *                and black.
 * @param centerX The x coordinate of the pattern origin.
 * @param centerY The y coordinate of the pattern origin.
 * @param angle   The rotation of the pattern in radians.
 * @param size    The diameter of a dot in pixels.
 */
function colorHalftone(centerX, centerY, angle, size) {
    gl.colorHalftone = gl.colorHalftone || new Shader(null, '\
        uniform sampler2D texture;\
        uniform vec2 center;\
        uniform float angle;\
        uniform float scale;\
        uniform vec2 texSize;\
        varying vec2 texCoord;\
        \
        float pattern(float angle) {\
            float s = sin(angle), c = cos(angle);\
            vec2 tex = texCoord * texSize - center;\
            vec2 point = vec2(\
                c * tex.x - s * tex.y,\
                s * tex.x + c * tex.y\
            ) * scale;\
            return (sin(point.x) * sin(point.y)) * 4.0;\
        }\
        \
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            vec3 cmy = 1.0 - color.rgb;\
            float k = min(cmy.x, min(cmy.y, cmy.z));\
            cmy = (cmy - k) / (1.0 - k);\
            cmy = clamp(cmy * 10.0 - 3.0 + vec3(pattern(angle + 0.26179), pattern(angle + 1.30899), pattern(angle)), 0.0, 1.0);\
            k = clamp(k * 10.0 - 5.0 + pattern(angle + 0.78539), 0.0, 1.0);\
            gl_FragColor = vec4(1.0 - cmy - k, color.a);\
        }\
    ');

    simpleShader.call(this, gl.colorHalftone, {
        center: [centerX, centerY],
        angle: angle,
        scale: Math.PI / size,
        texSize: [this.width, this.height]
    });

    return this;
}

// src/filters/fun/dotscreen.js
/**
 * @filter        Dot Screen
 * @description   Simulates a black and white halftone rendering of the image by multiplying
 *                pixel values with a rotated 2D sine wave pattern.
 * @param centerX The x coordinate of the pattern origin.
 * @param centerY The y coordinate of the pattern origin.
 * @param angle   The rotation of the pattern in radians.
 * @param size    The diameter of a dot in pixels.
 */
function dotScreen(centerX, centerY, angle, size) {
    gl.dotScreen = gl.dotScreen || new Shader(null, '\
        uniform sampler2D texture;\
        uniform vec2 center;\
        uniform float angle;\
        uniform float scale;\
        uniform vec2 texSize;\
        varying vec2 texCoord;\
        \
        float pattern() {\
            float s = sin(angle), c = cos(angle);\
            vec2 tex = texCoord * texSize - center;\
            vec2 point = vec2(\
                c * tex.x - s * tex.y,\
                s * tex.x + c * tex.y\
            ) * scale;\
            return (sin(point.x) * sin(point.y)) * 4.0;\
        }\
        \
        void main() {\
            vec4 color = texture2D(texture, texCoord);\
            float average = (color.r + color.g + color.b) / 3.0;\
            gl_FragColor = vec4(vec3(average * 10.0 - 5.0 + pattern()), color.a);\
        }\
    ');

    simpleShader.call(this, gl.dotScreen, {
        center: [centerX, centerY],
        angle: angle,
        scale: Math.PI / size,
        texSize: [this.width, this.height]
    });

    return this;
}

// src/filters/fun/edgework.js
/**
 * @filter       Edge Work
 * @description  Picks out different frequencies in the image by subtracting two
 *               copies of the image blurred with different radii.
 * @param radius The radius of the effect in pixels.
 */
function edgeWork(radius) {
    gl.edgeWork1 = gl.edgeWork1 || new Shader(null, '\
        uniform sampler2D texture;\
        uniform vec2 delta;\
        varying vec2 texCoord;\
        ' + randomShaderFunc + '\
        void main() {\
            vec2 color = vec2(0.0);\
            vec2 total = vec2(0.0);\
            \
            /* randomize the lookup values to hide the fixed number of samples */\
            float offset = random(vec3(12.9898, 78.233, 151.7182), 0.0);\
            \
            for (float t = -30.0; t <= 30.0; t++) {\
                float percent = (t + offset - 0.5) / 30.0;\
                float weight = 1.0 - abs(percent);\
                vec3 sample = texture2D(texture, texCoord + delta * percent).rgb;\
                float average = (sample.r + sample.g + sample.b) / 3.0;\
                color.x += average * weight;\
                total.x += weight;\
                if (abs(t) < 15.0) {\
                    weight = weight * 2.0 - 1.0;\
                    color.y += average * weight;\
                    total.y += weight;\
                }\
            }\
            gl_FragColor = vec4(color / total, 0.0, 1.0);\
        }\
    ');
    gl.edgeWork2 = gl.edgeWork2 || new Shader(null, '\
        uniform sampler2D texture;\
        uniform vec2 delta;\
        varying vec2 texCoord;\
        ' + randomShaderFunc + '\
        void main() {\
            vec2 color = vec2(0.0);\
            vec2 total = vec2(0.0);\
            \
            /* randomize the lookup values to hide the fixed number of samples */\
            float offset = random(vec3(12.9898, 78.233, 151.7182), 0.0);\
            \
            for (float t = -30.0; t <= 30.0; t++) {\
                float percent = (t + offset - 0.5) / 30.0;\
                float weight = 1.0 - abs(percent);\
                vec2 sample = texture2D(texture, texCoord + delta * percent).xy;\
                color.x += sample.x * weight;\
                total.x += weight;\
                if (abs(t) < 15.0) {\
                    weight = weight * 2.0 - 1.0;\
                    color.y += sample.y * weight;\
                    total.y += weight;\
                }\
            }\
            float c = clamp(10000.0 * (color.y / total.y - color.x / total.x) + 0.5, 0.0, 1.0);\
            gl_FragColor = vec4(c, c, c, 1.0);\
        }\
    ');

    simpleShader.call(this, gl.edgeWork1, {
        delta: [radius / this.width, 0]
    });
    simpleShader.call(this, gl.edgeWork2, {
        delta: [0, radius / this.height]
    });

    return this;
}

// src/filters/fun/hexagonalpixelate.js
/**
 * @filter        Hexagonal Pixelate
 * @description   Renders the image using a pattern of hexagonal tiles. Tile colors
 *                are nearest-neighbor sampled from the centers of the tiles.
 * @param centerX The x coordinate of the pattern center.
 * @param centerY The y coordinate of the pattern center.
 * @param scale   The width of an individual tile, in pixels.
 */
function hexagonalPixelate(centerX, centerY, scale) {
    gl.hexagonalPixelate = gl.hexagonalPixelate || new Shader(null, '\
        uniform sampler2D texture;\
        uniform vec2 center;\
        uniform float scale;\
        uniform vec2 texSize;\
        varying vec2 texCoord;\
        void main() {\
            vec2 tex = (texCoord * texSize - center) / scale;\
            tex.y /= 0.866025404;\
            tex.x -= tex.y * 0.5;\
            \
            vec2 a;\
            if (tex.x + tex.y - floor(tex.x) - floor(tex.y) < 1.0) a = vec2(floor(tex.x), floor(tex.y));\
            else a = vec2(ceil(tex.x), ceil(tex.y));\
            vec2 b = vec2(ceil(tex.x), floor(tex.y));\
            vec2 c = vec2(floor(tex.x), ceil(tex.y));\
            \
            vec3 TEX = vec3(tex.x, tex.y, 1.0 - tex.x - tex.y);\
            vec3 A = vec3(a.x, a.y, 1.0 - a.x - a.y);\
            vec3 B = vec3(b.x, b.y, 1.0 - b.x - b.y);\
            vec3 C = vec3(c.x, c.y, 1.0 - c.x - c.y);\
            \
            float alen = length(TEX - A);\
            float blen = length(TEX - B);\
            float clen = length(TEX - C);\
            \
            vec2 choice;\
            if (alen < blen) {\
                if (alen < clen) choice = a;\
                else choice = c;\
            } else {\
                if (blen < clen) choice = b;\
                else choice = c;\
            }\
            \
            choice.x += choice.y * 0.5;\
            choice.y *= 0.866025404;\
            choice *= scale / texSize;\
            gl_FragColor = texture2D(texture, choice + center / texSize);\
        }\
    ');

    simpleShader.call(this, gl.hexagonalPixelate, {
        center: [centerX, centerY],
        scale: scale,
        texSize: [this.width, this.height]
    });

    return this;
}

// src/filters/fun/ink.js
/**
 * @filter         Ink
 * @description    Simulates outlining the image in ink by darkening edges stronger than a
 *                 certain threshold. The edge detection value is the difference of two
 *                 copies of the image, each blurred using a blur of a different radius.
 * @param strength The multiplicative scale of the ink edges. Values in the range 0 to 1
 *                 are usually sufficient, where 0 doesn't change the image and 1 adds lots
 *                 of black edges. Negative strength values will create white ink edges
 *                 instead of black ones.
 */
function ink(strength) {
    gl.ink = gl.ink || new Shader(null, '\
        uniform sampler2D texture;\
        uniform float strength;\
        uniform vec2 texSize;\
        varying vec2 texCoord;\
        void main() {\
            vec2 dx = vec2(1.0 / texSize.x, 0.0);\
            vec2 dy = vec2(0.0, 1.0 / texSize.y);\
            vec4 color = texture2D(texture, texCoord);\
            float bigTotal = 0.0;\
            float smallTotal = 0.0;\
            vec3 bigAverage = vec3(0.0);\
            vec3 smallAverage = vec3(0.0);\
            for (float x = -2.0; x <= 2.0; x += 1.0) {\
                for (float y = -2.0; y <= 2.0; y += 1.0) {\
                    vec3 sample = texture2D(texture, texCoord + dx * x + dy * y).rgb;\
                    bigAverage += sample;\
                    bigTotal += 1.0;\
                    if (abs(x) + abs(y) < 2.0) {\
                        smallAverage += sample;\
                        smallTotal += 1.0;\
                    }\
                }\
            }\
            vec3 edge = max(vec3(0.0), bigAverage / bigTotal - smallAverage / smallTotal);\
            gl_FragColor = vec4(color.rgb - dot(edge, edge) * strength * 100000.0, color.a);\
        }\
    ');

    simpleShader.call(this, gl.ink, {
        strength: strength * strength * strength * strength * strength,
        texSize: [this.width, this.height]
    });

    return this;
}

// src/filters/warp/bulgepinch.js
/**
 * @filter         Bulge / Pinch
 * @description    Bulges or pinches the image in a circle.
 * @param centerX  The x coordinate of the center of the circle of effect.
 * @param centerY  The y coordinate of the center of the circle of effect.
 * @param radius   The radius of the circle of effect.
 * @param strength -1 to 1 (-1 is strong pinch, 0 is no effect, 1 is strong bulge)
 */
function bulgePinch(centerX, centerY, radius, strength) {
    gl.bulgePinch = gl.bulgePinch || warpShader('\
        uniform float radius;\
        uniform float strength;\
        uniform vec2 center;\
    ', '\
        coord -= center;\
        float distance = length(coord);\
        if (distance < radius) {\
            float percent = distance / radius;\
            if (strength > 0.0) {\
                coord *= mix(1.0, smoothstep(0.0, radius / distance, percent), strength * 0.75);\
            } else {\
                coord *= mix(1.0, pow(percent, 1.0 + strength * 0.75) * radius / distance, 1.0 - percent);\
            }\
        }\
        coord += center;\
    ');

    simpleShader.call(this, gl.bulgePinch, {
        radius: radius,
        strength: clamp(-1, strength, 1),
        center: [centerX, centerY],
        texSize: [this.width, this.height]
    });

    return this;
}

// src/filters/warp/matrixwarp.js
/**
 * @filter                Matrix Warp
 * @description           Transforms an image by a 2x2 or 3x3 matrix. The coordinates used in
 *                        the transformation are (x, y) for a 2x2 matrix or (x, y, 1) for a
 *                        3x3 matrix, where x and y are in units of pixels.
 * @param matrix          A 2x2 or 3x3 matrix represented as either a list or a list of lists.
 *                        For example, the 3x3 matrix [[2,0,0],[0,3,0],[0,0,1]] can also be
 *                        represented as [2,0,0,0,3,0,0,0,1] or just [2,0,0,3].
 * @param inverse         A boolean value that, when true, applies the inverse transformation
 *                        instead. (optional, defaults to false)
 * @param useTextureSpace A boolean value that, when true, uses texture-space coordinates
 *                        instead of screen-space coordinates. Texture-space coordinates range
 *                        from -1 to 1 instead of 0 to width - 1 or height - 1, and are easier
 *                        to use for simple operations like flipping and rotating.
 */
function matrixWarp(matrix, inverse, useTextureSpace) {
    gl.matrixWarp = gl.matrixWarp || warpShader('\
        uniform mat3 matrix;\
        uniform bool useTextureSpace;\
    ', '\
        if (useTextureSpace) coord = coord / texSize * 2.0 - 1.0;\
        vec3 warp = matrix * vec3(coord, 1.0);\
        coord = warp.xy / warp.z;\
        if (useTextureSpace) coord = (coord * 0.5 + 0.5) * texSize;\
    ');

    // Flatten all members of matrix into one big list
    matrix = Array.prototype.concat.apply([], matrix);

    // Extract a 3x3 matrix out of the arguments
    if (matrix.length == 4) {
        matrix = [
            matrix[0], matrix[1], 0,
            matrix[2], matrix[3], 0,
            0, 0, 1
        ];
    } else if (matrix.length != 9) {
        throw 'can only warp with 2x2 or 3x3 matrix';
    }

    simpleShader.call(this, gl.matrixWarp, {
        matrix: inverse ? getInverse(matrix) : matrix,
        texSize: [this.width, this.height],
        useTextureSpace: useTextureSpace | 0
    });

    return this;
}

// src/filters/warp/perspective.js
/**
 * @filter       Perspective
 * @description  Warps one quadrangle to another with a perspective transform. This can be used to
 *               make a 2D image look 3D or to recover a 2D image captured in a 3D environment.
 * @param before The x and y coordinates of four points before the transform in a flat list. This
 *               would look like [ax, ay, bx, by, cx, cy, dx, dy] for four points (ax, ay), (bx, by),
 *               (cx, cy), and (dx, dy).
 * @param after  The x and y coordinates of four points after the transform in a flat list, just
 *               like the other argument.
 */
function perspective(before, after) {
    var a = getSquareToQuad.apply(null, after);
    var b = getSquareToQuad.apply(null, before);
    var c = multiply(getInverse(a), b);
    return this.matrixWarp(c);
}

// src/filters/warp/swirl.js
/**
 * @filter        Swirl
 * @description   Warps a circular region of the image in a swirl.
 * @param centerX The x coordinate of the center of the circular region.
 * @param centerY The y coordinate of the center of the circular region.
 * @param radius  The radius of the circular region.
 * @param angle   The angle in radians that the pixels in the center of
 *                the circular region will be rotated by.
 */
function swirl(centerX, centerY, radius, angle) {
    gl.swirl = gl.swirl || warpShader('\
        uniform float radius;\
        uniform float angle;\
        uniform vec2 center;\
    ', '\
        coord -= center;\
        float distance = length(coord);\
        if (distance < radius) {\
            float percent = (radius - distance) / radius;\
            float theta = percent * percent * angle;\
            float s = sin(theta);\
            float c = cos(theta);\
            coord = vec2(\
                coord.x * c - coord.y * s,\
                coord.x * s + coord.y * c\
            );\
        }\
        coord += center;\
    ');

    simpleShader.call(this, gl.swirl, {
        radius: radius,
        center: [centerX, centerY],
        angle: angle,
        texSize: [this.width, this.height]
    });

    return this;
}

return exports;
})();


imagelib.drawing = {};

imagelib.drawing.context = function(size) {
  var canvas = document.createElement('canvas');
  canvas.width = size.w;
  canvas.height = size.h;
  canvas.style.setProperty('image-rendering', 'optimizeQuality', null);
  return canvas.getContext('2d');
};

imagelib.drawing.copy = function(dstCtx, src, size) {
  dstCtx.drawImage(src.canvas || src, 0, 0, size.w, size.h);
};

imagelib.drawing.clear = function(ctx, size) {
  ctx.clearRect(0, 0, size.w, size.h);
};

imagelib.drawing.drawCenterInside = function(dstCtx, src, dstRect, srcRect) {
  if (srcRect.w / srcRect.h > dstRect.w / dstRect.h) {
    var h = srcRect.h * dstRect.w / srcRect.w;
     imagelib.drawing.drawImageScaled(dstCtx, src,
        srcRect.x, srcRect.y,
        srcRect.w, srcRect.h,
        dstRect.x, dstRect.y + (dstRect.h - h) / 2,
        dstRect.w, h);
  } else {
    var w = srcRect.w * dstRect.h / srcRect.h;
     imagelib.drawing.drawImageScaled(dstCtx, src,
        srcRect.x, srcRect.y,
        srcRect.w, srcRect.h,
        dstRect.x + (dstRect.w - w) / 2, dstRect.y,
        w, dstRect.h);
  }
};

imagelib.drawing.drawCenterCrop = function(dstCtx, src, dstRect, srcRect) {
  if (srcRect.w / srcRect.h > dstRect.w / dstRect.h) {
    var w = srcRect.h * dstRect.w / dstRect.h;
    imagelib.drawing.drawImageScaled(dstCtx, src,
        srcRect.x + (srcRect.w - w) / 2, srcRect.y,
        w, srcRect.h,
        dstRect.x, dstRect.y,
        dstRect.w, dstRect.h);
  } else {
    var h = srcRect.w * dstRect.h / dstRect.w;
    imagelib.drawing.drawImageScaled(dstCtx, src,
        srcRect.x, srcRect.y + (srcRect.h - h) / 2,
        srcRect.w, h,
        dstRect.x, dstRect.y,
        dstRect.w, dstRect.h);
  }
};

imagelib.drawing.drawImageScaled = function(dstCtx, src, sx, sy, sw, sh, dx, dy, dw, dh) {
  if ((dw < sw / 2 && dh < sh / 2) && imagelib.ALLOW_MANUAL_RESCALE) {
    // scaling down by more than 50%, use an averaging algorithm since canvas.drawImage doesn't
    // do a good job by default
    sx = Math.floor(sx);
    sy = Math.floor(sy);
    sw =  Math.ceil(sw);
    sh =  Math.ceil(sh);
    dx = Math.floor(dx);
    dy = Math.floor(dy);
    dw =  Math.ceil(dw);
    dh =  Math.ceil(dh);

    var tmpCtx = imagelib.drawing.context({ w: sw, h: sh });
    tmpCtx.drawImage(src.canvas || src, -sx, -sy);
    var srcData = tmpCtx.getImageData(0, 0, sw, sh);

    var outCtx = imagelib.drawing.context({ w: dw, h: dh });
    var outData = outCtx.createImageData(dw, dh);

    var tr, tg, tb, ta; // R/G/B/A totals
    var numOpaquePixels;
    var numPixels;

    for (var y = 0; y < dh; y++) {
      for (var x = 0; x < dw; x++) {
        tr = tg = tb = ta = 0;
        numOpaquePixels = numPixels = 0;

        // Average the relevant region from source image
        for (var j = Math.floor(y * sh / dh); j < (y + 1) * sh / dh; j++) {
          for (var i = Math.floor(x * sw / dw); i < (x + 1) * sw / dw; i++) {
            ++numPixels;
            ta += srcData.data[(j * sw + i) * 4 + 3];
            if (srcData.data[(j * sw + i) * 4 + 3] == 0) {
              // disregard transparent pixels when computing average for R/G/B
              continue;
            }
            ++numOpaquePixels;
            tr += srcData.data[(j * sw + i) * 4 + 0];
            tg += srcData.data[(j * sw + i) * 4 + 1];
            tb += srcData.data[(j * sw + i) * 4 + 2];
          }
        }

        outData.data[(y * dw + x) * 4 + 0] = tr / numOpaquePixels;
        outData.data[(y * dw + x) * 4 + 1] = tg / numOpaquePixels;
        outData.data[(y * dw + x) * 4 + 2] = tb / numOpaquePixels;
        outData.data[(y * dw + x) * 4 + 3] = ta / numPixels;
      }
    }

    outCtx.putImageData(outData, 0, 0);
    dstCtx.drawImage(outCtx.canvas, dx, dy);

  } else {
    // use canvas.drawImage for all other cases, or if the page doesn't allow manual rescale
    dstCtx.drawImage(src.canvas || src, sx, sy, sw, sh, dx, dy, dw, dh);
  }
};

imagelib.drawing.trimRectWorkerJS_ = [
"self['onmessage'] = function(event) {                                       ",
"  var l = event.data.size.w, t = event.data.size.h, r = 0, b = 0;           ",
"                                                                            ",
"  var alpha;                                                                ",
"  for (var y = 0; y < event.data.size.h; y++) {                             ",
"    for (var x = 0; x < event.data.size.w; x++) {                           ",
"      alpha = event.data.imageData.data[                                    ",
"          ((y * event.data.size.w + x) << 2) + 3];                          ",
"      if (alpha >= event.data.minAlpha) {                                   ",
"        l = Math.min(x, l);                                                 ",
"        t = Math.min(y, t);                                                 ",
"        r = Math.max(x, r);                                                 ",
"        b = Math.max(y, b);                                                 ",
"      }                                                                     ",
"    }                                                                       ",
"  }                                                                         ",
"                                                                            ",
"  if (l > r) {                                                              ",
"    // no pixels, couldn't trim                                             ",
"    postMessage({ x: 0, y: 0, w: event.data.size.w, h: event.data.size.h });",
"    return;                                                                 ",
"  }                                                                         ",
"                                                                            ",
"  postMessage({ x: l, y: t, w: r - l + 1, h: b - t + 1 });                  ",
"};                                                                          ",
""].join('\n');

imagelib.drawing.getTrimRect = function(ctx, size, minAlpha, callback) {
  callback = callback || function(){};

  if (!ctx.canvas) {
    // Likely an image
    var src = ctx;
    ctx = imagelib.drawing.context(size);
    imagelib.drawing.copy(ctx, src, size);
  }

  if (minAlpha == 0)
    callback({ x: 0, y: 0, w: size.w, h: size.h });

  minAlpha = minAlpha || 1;

  var worker = imagelib.util.runWorkerJs(
      imagelib.drawing.trimRectWorkerJS_,
      {
        imageData: ctx.getImageData(0, 0, size.w, size.h),
        size: size,
        minAlpha: minAlpha
      },
      callback);

  return worker;
};

imagelib.drawing.getCenterOfMass = function(ctx, size, minAlpha, callback) {
  callback = callback || function(){};

  if (!ctx.canvas) {
    // Likely an image
    var src = ctx;
    ctx = imagelib.drawing.context(size);
    imagelib.drawing.copy(ctx, src, size);
  }

  if (minAlpha == 0)
    callback({ x: size.w / 2, y: size.h / 2 });

  minAlpha = minAlpha || 1;

  var l = size.w, t = size.h, r = 0, b = 0;
  var imageData = ctx.getImageData(0, 0, size.w, size.h);

  var sumX = 0;
  var sumY = 0;
  var n = 0; // number of pixels > minAlpha
  var alpha;
  for (var y = 0; y < size.h; y++) {
    for (var x = 0; x < size.w; x++) {
      alpha = imageData.data[((y * size.w + x) << 2) + 3];
      if (alpha >= minAlpha) {
        sumX += x;
        sumY += y;
        ++n;
      }
    }
  }

  if (n <= 0) {
    // no pixels > minAlpha, just use center
    callback({ x: size.w / 2, h: size.h / 2 });
  }

  callback({ x: Math.round(sumX / n), y: Math.round(sumY / n) });
};

imagelib.drawing.copyAsAlpha = function(dstCtx, src, size, onColor, offColor) {
  onColor = onColor || '#fff';
  offColor = offColor || '#000';

  dstCtx.save();
  dstCtx.clearRect(0, 0, size.w, size.h);
  dstCtx.globalCompositeOperation = 'source-over';
  imagelib.drawing.copy(dstCtx, src, size);
  dstCtx.globalCompositeOperation = 'source-atop';
  dstCtx.fillStyle = onColor;
  dstCtx.fillRect(0, 0, size.w, size.h);
  dstCtx.globalCompositeOperation = 'destination-atop';
  dstCtx.fillStyle = offColor;
  dstCtx.fillRect(0, 0, size.w, size.h);
  dstCtx.restore();
};

imagelib.drawing.makeAlphaMask = function(ctx, size, fillColor) {
  var src = ctx.getImageData(0, 0, size.w, size.h);
  var dst = ctx.createImageData(size.w, size.h);
  var srcData = src.data;
  var dstData = dst.data;
  var i, g;
  for (var y = 0; y < size.h; y++) {
    for (var x = 0; x < size.w; x++) {
      i = (y * size.w + x) << 2;
      g = 0.30 * srcData[i] +
              0.59 * srcData[i + 1] +
              0.11 * srcData[i + 2];
      dstData[i] = dstData[i + 1] = dstData[i + 2] = 255;
      dstData[i + 3] = g;
    }
  }
  ctx.putImageData(dst, 0, 0);

  if (fillColor) {
    ctx.save();
    ctx.globalCompositeOperation = 'source-atop';
    ctx.fillStyle = fillColor;
    ctx.fillRect(0, 0, size.w, size.h);
    ctx.restore();
  }
};

imagelib.drawing.applyFilter = function(filter, ctx, size) {
  var src = ctx.getImageData(0, 0, size.w, size.h);
  var dst = ctx.createImageData(size.w, size.h);
  filter.apply(src, dst);
  ctx.putImageData(dst, 0, 0);
};

(function() {
  function slowblur_(radius, ctx, size) {
    var rows = Math.ceil(radius);
    var r = rows * 2 + 1;
    var matrix = new Array(r * r);
    var sigma = radius / 3;
    var sigma22 = 2 * sigma * sigma;
    var sqrtPiSigma22 = Math.sqrt(Math.PI * sigma22);
    var radius2 = radius * radius;
    var total = 0;
    var index = 0;
    var distance2;
    for (var y = -rows; y <= rows; y++) {
      for (var x = -rows; x <= rows; x++) {
        distance2 = 1.0*x*x + 1.0*y*y;
        if (distance2 > radius2)
          matrix[index] = 0;
        else
          matrix[index] = Math.exp(-distance2 / sigma22) / sqrtPiSigma22;
        total += matrix[index];
        index++;
      }
    }

    imagelib.drawing.applyFilter(
        new ConvolutionFilter(matrix, total, 0, true),
        ctx, size);
  }

  function glfxblur_(radius, ctx, size) {
    var canvas = fx.canvas();
    var texture = canvas.texture(ctx.canvas);
    canvas.draw(texture).triangleBlur(radius).update();

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(canvas, 0, 0);
  }

  imagelib.drawing.blur = function(radius, ctx, size) {
    try {
      if (size.w > 128 || size.h > 128) {
        glfxblur_(radius, ctx, size);
      } else {
        slowblur_(radius, ctx, size);
      }

    } catch (e) {
      // WebGL unavailable, use the slower blur
      slowblur_(radius, ctx, size);
    }
  };
})();

imagelib.drawing.fx = function(effects, dstCtx, src, size) {
  effects = effects || [];

  var outerEffects = [];
  var innerEffects = [];
  var fillEffects = [];

  for (var i = 0; i < effects.length; i++) {
    if (/^outer/.test(effects[i].effect)) outerEffects.push(effects[i]);
    else if (/^inner/.test(effects[i].effect)) innerEffects.push(effects[i]);
    else if (/^fill/.test(effects[i].effect)) fillEffects.push(effects[i]);
  }

  var padLeft = 0, padTop, padRight, padBottom;
  var paddedSize;
  var tmpCtx, tmpCtx2;

  // Render outer effects
  for (var i = 0; i < outerEffects.length; i++) {
    padLeft = Math.max(padLeft, outerEffects[i].blur || 0); // blur radius
  }
  padTop = padRight = padBottom = padLeft;

  paddedSize = {
    w: size.w + padLeft + padRight,
    h: size.h + padTop + padBottom
  };

  tmpCtx = imagelib.drawing.context(paddedSize);

  for (var i = 0; i < outerEffects.length; i++) {
    var effect = outerEffects[i];

    dstCtx.save(); // D1
    tmpCtx.save(); // T1

    switch (effect.effect) {
      case 'outer-shadow':
        // The below method (faster) fails in Safari and other browsers, for some reason. Likely
        // something to do with the source-atop blending mode.
        // TODO: investigate why it fails in Safari

        // imagelib.drawing.clear(tmpCtx, size);
        // imagelib.drawing.copy(tmpCtx, src.canvas || src, size);
        // if (effect.blur)
        //   imagelib.drawing.blur(effect.blur, tmpCtx, size);
        // tmpCtx.globalCompositeOperation = 'source-atop';
        // tmpCtx.fillStyle = effect.color || '#000';
        // tmpCtx.fillRect(0, 0, size.w, size.h);
        // if (effect.translate)
        //   dstCtx.translate(effect.translate.x || 0, effect.translate.y || 0);
        // 
        // dstCtx.globalAlpha = Math.max(0, Math.min(1, effect.opacity || 1));
        // imagelib.drawing.copy(dstCtx, tmpCtx, size);

        imagelib.drawing.clear(tmpCtx, paddedSize);

        tmpCtx.save(); // T2
        tmpCtx.translate(padLeft, padTop);
        imagelib.drawing.copyAsAlpha(tmpCtx, src.canvas || src, size);
        tmpCtx.restore(); // T2

        if (effect.blur)
          imagelib.drawing.blur(effect.blur, tmpCtx, paddedSize);

        imagelib.drawing.makeAlphaMask(tmpCtx, paddedSize, effect.color || '#000');
        if (effect.translate)
          dstCtx.translate(effect.translate.x || 0, effect.translate.y || 0);

        dstCtx.globalAlpha = Math.max(0, Math.min(1, effect.opacity || 1));
        dstCtx.translate(-padLeft, -padTop);
        imagelib.drawing.copy(dstCtx, tmpCtx, paddedSize);
        break;
    }

    dstCtx.restore(); // D1
    tmpCtx.restore(); // T1
  }

  dstCtx.save(); // D1

  // Render object with optional fill effects (only take first fill effect)
  tmpCtx = imagelib.drawing.context(size);

  imagelib.drawing.clear(tmpCtx, size);
  imagelib.drawing.copy(tmpCtx, src.canvas || src, size);
  var fillOpacity = 1.0;

  if (fillEffects.length) {
    var effect = fillEffects[0];

    tmpCtx.save(); // T1
    tmpCtx.globalCompositeOperation = 'source-atop';

    switch (effect.effect) {
      case 'fill-color':
        tmpCtx.fillStyle = effect.color;
        break;

      case 'fill-lineargradient':
        var gradient = tmpCtx.createLinearGradient(
            effect.from.x, effect.from.y, effect.to.x, effect.to.y);
        for (var i = 0; i < effect.colors.length; i++) {
          gradient.addColorStop(effect.colors[i].offset, effect.colors[i].color);
        }
        tmpCtx.fillStyle = gradient;
        break;
    }

    fillOpacity = Math.max(0, Math.min(1, effect.opacity || 1));

    tmpCtx.fillRect(0, 0, size.w, size.h);
    tmpCtx.restore(); // T1
  }

  dstCtx.globalAlpha = fillOpacity;
  imagelib.drawing.copy(dstCtx, tmpCtx, size);
  dstCtx.globalAlpha = 1.0;

  // Render inner effects
  var translate;
  padLeft = padTop = padRight = padBottom = 0;
  for (var i = 0; i < innerEffects.length; i++) {
    translate = effect.translate || {};
    padLeft   = Math.max(padLeft,   (innerEffects[i].blur || 0) + Math.max(0,  translate.x || 0));
    padTop    = Math.max(padTop,    (innerEffects[i].blur || 0) + Math.max(0,  translate.y || 0));
    padRight  = Math.max(padRight,  (innerEffects[i].blur || 0) + Math.max(0, -translate.x || 0));
    padBottom = Math.max(padBottom, (innerEffects[i].blur || 0) + Math.max(0, -translate.y || 0));
  }

  paddedSize = {
    w: size.w + padLeft + padRight,
    h: size.h + padTop + padBottom
  };

  tmpCtx = imagelib.drawing.context(paddedSize);
  tmpCtx2 = imagelib.drawing.context(paddedSize);

  for (var i = 0; i < innerEffects.length; i++) {
    var effect = innerEffects[i];

    dstCtx.save(); // D2
    tmpCtx.save(); // T1

    switch (effect.effect) {
      case 'inner-shadow':
        imagelib.drawing.clear(tmpCtx, paddedSize);

        tmpCtx.save(); // T2
        tmpCtx.translate(padLeft, padTop);
        imagelib.drawing.copyAsAlpha(tmpCtx, src.canvas || src, size, '#fff', '#000');
        tmpCtx.restore(); // T2

        tmpCtx2.save(); // T2
        tmpCtx2.translate(padLeft, padTop);
        imagelib.drawing.copyAsAlpha(tmpCtx2, src.canvas || src, size);
        tmpCtx2.restore(); // T2

        if (effect.blur)
          imagelib.drawing.blur(effect.blur, tmpCtx2, paddedSize);
        imagelib.drawing.makeAlphaMask(tmpCtx2, paddedSize, '#000');
        if (effect.translate)
          tmpCtx.translate(effect.translate.x || 0, effect.translate.y || 0);

        tmpCtx.globalCompositeOperation = 'source-over';
        imagelib.drawing.copy(tmpCtx, tmpCtx2, paddedSize);

        imagelib.drawing.makeAlphaMask(tmpCtx, paddedSize, effect.color);
        dstCtx.globalAlpha = Math.max(0, Math.min(1, effect.opacity || 1));
        dstCtx.translate(-padLeft, -padTop);
        imagelib.drawing.copy(dstCtx, tmpCtx, paddedSize);
        break;
    }

    tmpCtx.restore(); // T1
    dstCtx.restore(); // D2
  };

  dstCtx.restore(); // D1
};

imagelib.loadImageResources = function(images, callback) {
  var imageResources = {};

  var checkForCompletion = function() {
    for (var id in images) {
      if (!(id in imageResources))
        return;
    }
    (callback || function(){})(imageResources);
    callback = null;
  };

  for (var id in images) {
    var img = document.createElement('img');
    img.src = images[id];
    (function(img, id) {
      img.onload = function() {
        imageResources[id] = img;
        checkForCompletion();
      };
      img.onerror = function() {
        imageResources[id] = null;
        checkForCompletion();
      }
    })(img, id);
  }
};

imagelib.loadFromUri = function(uri, callback) {
  callback = callback || function(){};

  var img = document.createElement('img');
  img.src = uri;
  img.onload = function() {
    callback(img);
  };
  img.onerror = function() {
    callback(null);
  }
};

imagelib.toDataUri = function(img) {
  var canvas = document.createElement('canvas');
  canvas.width = img.naturalWidth;
  canvas.height = img.naturalHeight;

  var ctx = canvas.getContext('2d');
  ctx.drawImage(img, 0, 0);

  return canvas.toDataURL();
};

imagelib.util = {};

/**
 * Helper method for running inline Web Workers, if the browser can support
 * them. If the browser doesn't support inline Web Workers, run the script
 * on the main thread, with this function body's scope, using eval. Browsers
 * must provide BlobBuilder, URL.createObjectURL, and Worker support to use
 * inline Web Workers. Most features such as importScripts() are not
 * currently supported, so this only works for basic workers.
 * @param {String} js The inline Web Worker Javascript code to run. This code
 *     must use 'self' and not 'this' as the global context variable.
 * @param {Object} params The parameters object to pass to the worker.
 *     Equivalent to calling Worker.postMessage(params);
 * @param {Function} callback The callback to run when the worker calls
 *     postMessage. Equivalent to adding a 'message' event listener on a
 *     Worker object and running callback(event.data);
 */
imagelib.util.runWorkerJs = function(js, params, callback) {
  var URL = window.URL || window.webkitURL || window.mozURL;
  var Worker = window.Worker;

  if (URL && Worker && imagelib.util.hasBlobConstructor()) {
    // The Blob constructor, Worker, and window.URL.createObjectURL are all available,
    // so we can use inline workers.
    var bb = new Blob([js], {type:'text/javascript'});
    var worker = new Worker(URL.createObjectURL(bb));
    worker.onmessage = function(event) {
      callback(event.data);
    };
    worker.postMessage(params);
    return worker;

  } else {
    // We can't use inline workers, so run the worker JS on the main thread.
    (function() {
      var __DUMMY_OBJECT__ = {};
      // Proxy to Worker.onmessage
      var postMessage = function(result) {
        callback(result);
      };
      // Bind the worker to this dummy object. The worker will run
      // in this scope.
      eval('var self=__DUMMY_OBJECT__;\n' + js);
      // Proxy to Worker.postMessage
      __DUMMY_OBJECT__.onmessage({
        data: params
      });
    })();

    // Return a dummy Worker.
    return {
      terminate: function(){}
    };
  }
};

// https://github.com/gildas-lormeau/zip.js/issues/17#issuecomment-8513258
// thanks Eric!
imagelib.util.hasBlobConstructor = function() {
  try {
    return !!new Blob();
  } catch(e) {
    return false;
  }
};

// http://en.wikipedia.org/wiki/Adler32
imagelib.util.adler32 = function(arr) {
  arr = arr || [];
  var adler = new imagelib.util.Adler32();
  for (var i = 0; i < arr.length; i++) {
    adler.addNext(arr[i]);
  }
  return adler.compute();
};

imagelib.util.Adler32 = function() {
  this.reset();
};
imagelib.util.Adler32._MOD_ADLER = 65521;
imagelib.util.Adler32.prototype.reset = function() {
  this._a = 1;
  this._b = 0;
  this._index = 0;
};
imagelib.util.Adler32.prototype.addNext = function(value) {
  this._a = (this._a + value) % imagelib.util.Adler32._MOD_ADLER;
  this._b = (this._b + this._a) % imagelib.util.Adler32._MOD_ADLER;
};
imagelib.util.Adler32.prototype.compute = function() {
  return (this._b << 16) | this._a;
};

imagelib.util.Summer = imagelib.util.Adler32;
imagelib.util.Summer.prototype = imagelib.util.Adler32.prototype;

window.imagelib = imagelib;

})();

(function() {

var studio = {};

studio.checkBrowser = function() {
  var chromeMatch = navigator.userAgent.match(/Chrome\/(\d+)/);
  var browserSupported = false;
  if (chromeMatch) {
    var chromeVersion = parseInt(chromeMatch[1], 10);
    if (chromeVersion >= 6) {
      browserSupported = true;
    }
  }

  if (!browserSupported) {
    $('<div>')
      .addClass('browser-unsupported-note ui-state-highlight')
      .attr('title', 'Your browser is not supported.')
      .append($('<span class="ui-icon ui-icon-alert" ' +
                'style="float:left; margin:0 7px 50px 0;">'))
      .append($('<p>')
        .html('Currently only ' +
              '<a href="http://www.google.com/chrome">Google Chrome</a> ' +
              'is recommended and supported. Your mileage may vary with ' +
              'other browsers.'))
      .prependTo('body');
  }
};

// format: #INSERTFILE "../../lib/thefile.js"

studio.forms = {};

/**
 * Class defining a data entry form for use in the Asset Studio.
 */
studio.forms.Form = Base.extend({
  /**
   * Creates a new form with the given parameters.
   * @constructor
   * @param {Function} [params.onChange] A function
   * @param {Array} [params.inputs] A list of inputs
   */
  constructor: function(id, params) {
    this.id_ = id;
    this.params_ = params;
    this.fields_ = params.fields;
    this.pauseNotify_ = false;

    for (var i = 0; i < this.fields_.length; i++) {
      this.fields_[i].setForm_(this);
    }

    this.onChange = this.params_.onChange || function(){};
  },

  /**
   * Creates the user interface for the form in the given container.
   * @private
   * @param {HTMLElement} container The container node for the form UI.
   */
  createUI: function(container) {
    for (var i = 0; i < this.fields_.length; i++) {
      var field = this.fields_[i];
      field.createUI(container);
    }
  },

  /**
   * Notifies that the form contents have changed;
   * @private
   */
  notifyChanged_: function(field) {
    if (this.pauseNotify_) {
      return;
    }
    this.onChange(field);
  },

  /**
   * Returns the current values of the form fields, as an object.
   * @type Object
   */
  getValues: function() {
    var values = {};

    for (var i = 0; i < this.fields_.length; i++) {
      var field = this.fields_[i];
      values[field.id_] = field.getValue();
    }

    return values;
  },

  /**
   * Returns all available serialized values of the form fields, as an object.
   * All values in the returned object are either strings or objects.
   * @type Object
   */
  getValuesSerialized: function() {
    var values = {};

    for (var i = 0; i < this.fields_.length; i++) {
      var field = this.fields_[i];
      var value = field.serializeValue ? field.serializeValue() : undefined;
      if (value !== undefined) {
        values[field.id_] = field.serializeValue();
      }
    }

    return values;
  },

  /**
   * Sets the form field values for the key/value pairs in the given object.
   * Values must be serialized forms of the form values. The form must be
   * initialized before calling this method.
   */
  setValuesSerialized: function(serializedValues) {
    this.pauseNotify_ = true;
    for (var i = 0; i < this.fields_.length; i++) {
      var field = this.fields_[i];
      if (field.id_ in serializedValues && field.deserializeValue) {
        field.deserializeValue(serializedValues[field.id_]);
      }
    }
    this.pauseNotify_ = false;
    this.notifyChanged_(null);
  }
});


/**
 * Represents a form field and its associated UI elements. This should be
 * broken out into a more MVC-like architecture in the future.
 */
studio.forms.Field = Base.extend({
  /**
   * Instantiates a new field with the given ID and parameters.
   * @constructor
   */
  constructor: function(id, params) {
    this.id_ = id;
    this.params_ = params;
  },

  /**
   * Sets the form owner of the field. Internally called by
   * {@link studio.forms.Form}.
   * @private
   * @param {studio.forms.Form} form The owner form.
   */
  setForm_: function(form) {
    this.form_ = form;
  },

  /**
   * Returns a complete ID.
   * @type String
   */
  getLongId: function() {
    return this.form_.id_ + '-' + this.id_;
  },

  /**
   * Returns the ID for the form's UI element (or container).
   * @type String
   */
  getHtmlId: function() {
    return '_frm-' + this.getLongId();
  },

  /**
   * Generates the UI elements for a form field container. Not very portable
   * outside the Asset Studio UI. Intended to be overriden by descendents.
   * @private
   * @param {HTMLElement} container The destination element to contain the
   * field.
   */
  createUI: function(container) {
    container = $(container);
    this.baseEl_ = $('<div>')
      .addClass('form-field-outer')
      .append(
        $('<label>')
          .attr('for', this.getHtmlId())
          .text(this.params_.title)
          .append($('<div>')
            .addClass('form-field-help-text')
            .css('display', this.params_.helpText ? '' : 'none')
            .html(this.params_.helpText))
      )
      .append(
        $('<div>')
          .addClass('form-field-container')
      )
      .appendTo(container);
    return this.baseEl_;
  },

  /**
   * Enables or disables the form field.
   */
  setEnabled: function(enabled) {
    if (this.baseEl_) {
      if (enabled) {
        this.baseEl_.removeAttr('disabled');
      } else {
        this.baseEl_.attr('disabled', 'disabled');
      }
    }
  }
});

studio.forms.TextField = studio.forms.Field.extend({
  createUI: function(container) {
    var fieldContainer = $('.form-field-container', this.base(container));
    var me = this;

    this.el_ = $('<input>')
      .attr('type', 'text')
      .addClass('form-text')
      .val(this.getValue())
      .bind('change', function() {
        me.setValue($(this).val(), true);
      })
      .bind('keydown change', function() {
        var inputEl = this;
        var oldVal = me.getValue();
        window.setTimeout(function() {
          var newVal = $(inputEl).val();
          if (oldVal != newVal) {
            me.setValue(newVal, true);
          }
        }, 0);
      })
      .appendTo(fieldContainer);
  },

  getValue: function() {
    var value = this.value_;
    if (typeof value != 'string') {
      value = this.params_.defaultValue || '';
    }
    return value;
  },

  setValue: function(val, pauseUi) {
    this.value_ = val;
    if (!pauseUi) {
      $(this.el_).val(val);
    }
    this.form_.notifyChanged_(this);
  },

  serializeValue: function() {
    return this.getValue();
  },

  deserializeValue: function(s) {
    this.setValue(s);
  }
});

studio.forms.AutocompleteTextField = studio.forms.Field.extend({
  createUI: function(container) {
    var fieldContainer = $('.form-field-container', this.base(container));
    var me = this;

    var datalistId = this.getHtmlId() + '-datalist';

    this.el_ = $('<input>')
      .attr('type', 'text')
      .addClass('form-text')
      .attr('list', datalistId)
      .val(this.getValue())
      .bind('keydown change', function() {
        var inputEl = this;
        window.setTimeout(function() {
          me.setValue($(inputEl).val(), true);
        }, 0);
      })
      .appendTo(fieldContainer);

    this.datalistEl_ = $('<datalist>')
      .attr('id', datalistId)
      .appendTo(fieldContainer);

    this.params_.items = this.params_.items || [];
    for (var i = 0; i < this.params_.items.length; i++) {
      this.datalistEl_.append($('<option>').attr('value', this.params_.items[i]));
    }
  },

  getValue: function() {
    var value = this.value_;
    if (typeof value != 'string') {
      value = this.params_.defaultValue || '';
    }
    return value;
  },

  setValue: function(val, pauseUi) {
    this.value_ = val;
    if (!pauseUi) {
      $(this.el_).val(val);
    }
    this.form_.notifyChanged_(this);
  },

  serializeValue: function() {
    return this.getValue();
  },

  deserializeValue: function(s) {
    this.setValue(s);
  }
});

studio.forms.ColorField = studio.forms.Field.extend({
  createUI: function(container) {
    var fieldContainer = $('.form-field-container', this.base(container));
    var me = this;
    this.el_ = $('<input>')
      .addClass('form-color')
      .attr('type', 'text')
      .attr('id', this.getHtmlId())
      .css('background-color', this.getValue().color)
      .appendTo(fieldContainer);

    this.el_.spectrum({
      color: this.getValue().color,
      showInput: true,
      showPalette: true,
      palette: [
        ['#fff', '#000'],
        ['#33b5e5', '#09c'],
        ['#a6c', '#93c'],
        ['#9c0', '#690'],
        ['#fb3', '#f80'],
        ['#f44', '#c00']
      ],
      localStorageKey: 'recentcolors',
      showInitial: true,
      showButtons: false,
      change: function(color) {
        me.setValue({ color: color.toHexString() }, true);
      }
    });

    if (this.params_.alpha) {
      this.alphaEl_ = $('<input>')
        .attr('type', 'range')
        .attr('min', 0)
        .attr('max', 100)
        .val(this.getValue().alpha)
        .addClass('form-range')
        .change(function() {
    			me.setValue({ alpha: Number(me.alphaEl_.val()) }, true);
        })
        .appendTo(fieldContainer);

      this.alphaTextEl_ = $('<div>')
        .addClass('form-range-text')
        .text(this.getValue().alpha + '%')
        .appendTo(fieldContainer);
    }
  },

  getValue: function() {
    var color = this.value_ || this.params_.defaultValue || '#000000';
    if (/^([0-9a-f]{6}|[0-9a-f]{3})$/i.test(color)) {
      color = '#' + color;
    }

    var alpha = this.alpha_;
    if (typeof alpha != 'number') {
      alpha = this.params_.defaultAlpha;
      if (typeof alpha != 'number')
        alpha = 100;
    }

    return { color: color, alpha: alpha };
  },

  setValue: function(val, pauseUi) {
    val = val || {};
    if ('color' in val) {
      this.value_ = val.color;
    }
    if ('alpha' in val) {
      this.alpha_ = val.alpha;
    }

    var computedValue = this.getValue();
    this.el_.css('background-color', computedValue.color);
    if (!pauseUi) {
      $(this.el_).spectrum('set', computedValue.color);
      if (this.alphaEl_) {
        this.alphaEl_.val(computedValue.alpha);
      }
    }
    if (this.alphaTextEl_) {
      this.alphaTextEl_.text(computedValue.alpha + '%');
    }
    this.form_.notifyChanged_(this);
  },

  serializeValue: function() {
    var computedValue = this.getValue();
    return computedValue.color.replace(/^#/, '') + ',' + computedValue.alpha;
  },

  deserializeValue: function(s) {
    var val = {};
    var arr = s.split(',', 2);
    if (arr.length >= 1) {
      val.color = arr[0];
    }
    if (arr.length >= 2) {
      val.alpha = parseInt(arr[1], 10);
    }
    this.setValue(val);
  }
});

studio.forms.EnumField = studio.forms.Field.extend({
  createUI: function(container) {
    var fieldContainer = $('.form-field-container', this.base(container));
    var me = this;

    if (this.params_.buttons) {
      this.el_ = $('<div>')
        .attr('id', this.getHtmlId())
        .addClass('form-field-buttonset')
        .appendTo(fieldContainer);
      for (var i = 0; i < this.params_.options.length; i++) {
        var option = this.params_.options[i];
        $('<input>')
          .attr({
            type: 'radio',
            name: this.getHtmlId(),
            id: this.getHtmlId() + '-' + option.id,
            value: option.id
          })
          .change(function() {
            me.setValueInternal_($(this).val(), true);
          })
          .appendTo(this.el_);
        $('<label>')
          .attr('for', this.getHtmlId() + '-' + option.id)
          .html(option.title)
          .appendTo(this.el_);
      }
      this.setValueInternal_(this.getValue());

    } else {
      this.el_ = $('<select>')
        .attr('id', this.getHtmlId())
        .change(function() {
          me.setValueInternal_($(this).val(), true);
        })
        .appendTo(fieldContainer);
      for (var i = 0; i < this.params_.options.length; i++) {
        var option = this.params_.options[i];
        $('<option>')
          .attr('value', option.id)
          .text(option.title)
          .appendTo(this.el_);
      }

      this.el_.combobox({
        selected: function(evt, data) {
          me.setValueInternal_(data.item.value, true);
          me.form_.notifyChanged_(me);
        }
      });
      this.setValueInternal_(this.getValue());
    }
  },

  getValue: function() {
    var value = this.value_;
    if (value === undefined) {
      value = this.params_.defaultValue || this.params_.options[0].id;
    }
    return value;
  },

  setValue: function(val, pauseUi) {
    this.setValueInternal_(val, pauseUi);
  },

  setValueInternal_: function(val, pauseUi) {
    // Note, this needs to be its own function because setValue gets
    // overridden in BooleanField and we need access to this method
    // from createUI.
    this.value_ = val;
    if (!pauseUi) {
      if (this.params_.buttons) {
        $('input', this.el_).each(function(i, el) {
          $(el).attr('checked', $(el).val() == val);
        });
      } else {
        this.el_.val(val);
      }
    }
    this.form_.notifyChanged_(this);
  },

  serializeValue: function() {
    return this.getValue();
  },

  deserializeValue: function(s) {
    this.setValue(s);
  }
});

studio.forms.BooleanField = studio.forms.EnumField.extend({
  constructor: function(id, params) {
    params.options = [
      { id: '1', title: params.onText || 'Yes' },
      { id: '0', title: params.offText || 'No' }
    ];
    params.defaultValue = params.defaultValue ? '1' : '0';
    params.buttons = true;
    this.base(id, params);
  },

  getValue: function() {
    return this.base() == '1';
  },

  setValue: function(val, pauseUi) {
    this.base(val ? '1' : '0', pauseUi);
  },

  serializeValue: function() {
    return this.getValue() ? '1' : '0';
  },

  deserializeValue: function(s) {
    this.setValue(s == '1');
  }
});

studio.forms.RangeField = studio.forms.Field.extend({
  createUI: function(container) {
    var fieldContainer = $('.form-field-container', this.base(container));
    var me = this;

    this.el_ = $('<input>')
      .attr('type', 'range')
      .attr('min', this.params_.min || 0)
      .attr('max', this.params_.max || 100)
      .attr('step', this.params_.step || 1)
      .addClass('form-range')
      .change(function() {
        me.setValue(Number(me.el_.val()) || 0, true);
      })
      .val(this.getValue())
      .appendTo(fieldContainer);

    if (this.params_.textFn || this.params_.showText) {
      this.params_.textFn = this.params_.textFn || function(d){ return d; };
      this.textEl_ = $('<div>')
        .addClass('form-range-text')
        .text(this.params_.textFn(this.getValue()))
        .appendTo(fieldContainer);
    }
  },

  getValue: function() {
    var value = this.value_;
    if (typeof value != 'number') {
      value = this.params_.defaultValue;
      if (typeof value != 'number')
        value = 0;
    }
    return value;
  },

  setValue: function(val, pauseUi) {
    this.value_ = val;
    if (!pauseUi) {
      this.el_.val(val);
    }
		if (this.textEl_) {
		  this.textEl_.text(this.params_.textFn(val));
	  }
		this.form_.notifyChanged_(this);
  },

  serializeValue: function() {
    return this.getValue();
  },

  deserializeValue: function(s) {
    this.setValue(Number(s)); // don't use parseInt nor parseFloat
  }
});

studio.hash = {};

studio.hash.boundFormOldOnChange_ = null;
studio.hash.boundForm_ = null;
studio.hash.currentParams_ = {};
studio.hash.currentHash_ = null; // The URI encoded, currently loaded state.

studio.hash.bindFormToDocumentHash = function(form) {
  if (!studio.hash.boundForm_) {
    // Checks for changes in the document hash
    // and reloads the form if necessary.
    var hashChecker_ = function() {
      // Don't use document.location.hash because it automatically
      // resolves URI-escaped entities.
      var docHash = studio.hash.paramsToHash(studio.hash.hashToParams(
          (document.location.href.match(/#.*/) || [''])[0]));

      if (docHash != studio.hash.currentHash_) {
        var newHash = docHash;
        var newParams = studio.hash.hashToParams(newHash);

        studio.hash.onHashParamsChanged_(newParams);
        studio.hash.currentParams_ = newParams;
        studio.hash.currentHash_ = newHash;
      };

      window.setTimeout(hashChecker_, 100);
    }

    window.setTimeout(hashChecker_, 0);
  }

  if (studio.hash.boundFormOldOnChange_ && studio.hash.boundForm_) {
    studio.hash.boundForm_.onChange = studio.hash.boundFormOldOnChange_;
  }

  studio.hash.boundFormOldOnChange_ = form.onChange;

  studio.hash.boundForm_ = form;
  var formChangeTimeout = null;
  studio.hash.boundForm_.onChange = function() {
    if (formChangeTimeout) {
      window.clearTimeout(formChangeTimeout);
    }
    formChangeTimeout = window.setTimeout(function() {
      studio.hash.onFormChanged_();
    }, 500);
    (studio.hash.boundFormOldOnChange_ || function(){}).apply(form, arguments);
  };
};

studio.hash.onHashParamsChanged_ = function(newParams) {
  if (studio.hash.boundForm_) {
    studio.hash.boundForm_.setValuesSerialized(newParams);
  }
};

studio.hash.onFormChanged_ = function() {
  if (studio.hash.boundForm_) {
    // We set this to prevent feedback in the hash checker.
    studio.hash.currentParams_ = studio.hash.boundForm_.getValuesSerialized();
    studio.hash.currentHash_ = studio.hash.paramsToHash(
        studio.hash.currentParams_);
    document.location.hash = studio.hash.currentHash_;
  }
};

studio.hash.hashToParams = function(hash) {
  var params = {};
  hash = hash.replace(/^[?#]/, '');

  var pairs = hash.split('&');
  for (var i = 0; i < pairs.length; i++) {
    var parts = pairs[i].split('=', 2);

    // Most of the time path == key, but for objects like a.b=1, we need to
    // descend into the hierachy.
    var path = parts[0] ? decodeURIComponent(parts[0]) : parts[0];
    var val = parts[1] ? decodeURIComponent(parts[1]) : parts[1];
    var pathArr = path.split('.');
    var obj = params;
    for (var j = 0; j < pathArr.length - 1; j++) {
      obj[pathArr[j]] = obj[pathArr[j]] || {};
      obj = obj[pathArr[j]];
    }
    var key = pathArr[pathArr.length - 1];
    if (key in obj) {
      // Handle array values.
      if (obj[key] && obj[key].splice) {
        obj[key].push(val);
      } else {
        obj[key] = [obj[key], val];
      }
    } else {
      obj[key] = val;
    }
  }

  return params;
};

studio.hash.paramsToHash = function(params, prefix) {
  var hashArr = [];

  var keyPath_ = function(k) {
    return encodeURIComponent((prefix ? prefix + '.' : '') + k);
  };

  var pushKeyValue_ = function(k, v) {
    if (v === false) v = 0;
    if (v === true)  v = 1;
    hashArr.push(keyPath_(k) + '=' +
                 encodeURIComponent(v.toString()));
  };

  for (var key in params) {
    var val = params[key];
    if (val === undefined || val === null) {
      continue;
    }

    if (typeof val == 'object') {
      if (val.splice && val.length) {
        // Arrays
        for (var i = 0; i < val.length; i++) {
          pushKeyValue_(key, val[i]);
        }
      } else {
        // Objects
        hashArr.push(studio.hash.paramsToHash(val, keyPath_(key)));
      }
    } else {
      // All other values
      pushKeyValue_(key, val);
    }
  }

  return hashArr.join('&');
};


/**
 * This is needed due to what seems like a bug in Chrome where using drawImage
 * with any SVG, regardless of origin (even if it was loaded from a data URI),
 * marks the canvas's origin dirty flag, precluding us from accessing its pixel
 * data.
 */
var USE_CANVG = window.canvg && true;

/**
 * Represents a form field for image values.
 */
studio.forms.ImageField = studio.forms.Field.extend({
  constructor: function(id, params) {
    this.valueType_ = null;
    this.textParams_ = {};
    this.imageParams_ = {};
    this.spaceFormValues_ = {}; // cache
    this.base(id, params);
  },

  createUI: function(container) {
    var fieldUI = this.base(container);
    var fieldContainer = $('.form-field-container', fieldUI);

    var me = this;

    // Set up drag+drop on the entire field container
    fieldUI.addClass('form-field-drop-target');
    fieldUI.get(0).ondragenter = studio.forms.ImageField.makeDragenterHandler_(
      fieldUI);
    fieldUI.get(0).ondragleave = studio.forms.ImageField.makeDragleaveHandler_(
      fieldUI);
    fieldUI.get(0).ondragover = studio.forms.ImageField.makeDragoverHandler_(
      fieldUI);
    fieldUI.get(0).ondrop = studio.forms.ImageField.makeDropHandler_(fieldUI,
      function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        studio.forms.ImageField.loadImageFromFileList(evt.dataTransfer.files, function(ret) {
          if (!ret)
            return;

          me.setValueType_('image');
          me.imageParams_ = ret;
          me.valueFilename_ = ret.name;
          me.renderValueAndNotifyChanged_();
        });
      });

    // Create radio buttons
    this.el_ = $('<div>')
      .attr('id', this.getHtmlId())
      .addClass('form-field-buttonset')
      .appendTo(fieldContainer);

    var types;
    if (this.params_.imageOnly) {
      types = [
        'image', 'Select Image'
      ];
    } else {
      types = [
        'image', 'Image',
        'clipart', 'Clipart',
        'text', 'Text'
      ];
    }

    var typeEls = {};

    for (var i = 0; i < types.length / 2; i++) {
      $('<input>')
        .attr({
          type: 'radio',
          name: this.getHtmlId(),
          id: this.getHtmlId() + '-' + types[i * 2],
          value: types[i * 2]
        })
        .appendTo(this.el_);
      typeEls[types[i * 2]] = $('<label>')
        .attr('for', this.getHtmlId() + '-' + types[i * 2])
        .text(types[i * 2 + 1])
        .appendTo(this.el_);
    }

    // Prepare UI for the 'image' type
    this.fileEl_ = $('<input>')
      .addClass('form-image-hidden-file-field')
      .attr({
        id: this.getHtmlId(),
        type: 'file',
        accept: 'image/*'
      })
      .change(function() {
        studio.forms.ImageField.loadImageFromFileList(me.fileEl_.get(0).files, function(ret) {
          if (!ret)
            return;

          me.setValueType_('image');
          me.imageParams_ = ret;
          me.valueFilename_ = ret.name;
          me.renderValueAndNotifyChanged_();
        });
      })
      .appendTo(this.el_);

    typeEls.image.click(function(evt) {
      me.fileEl_.trigger('click');
      me.setValueType_(null);
      me.renderValueAndNotifyChanged_();
      evt.preventDefault();
      return false;
    });

    // Prepare UI for the 'clipart' type
    if (!this.params_.imageOnly) {
      var clipartParamsEl = $('<div>')
        .addClass('form-image-type-params form-image-type-params-clipart')
        .hide()
        .appendTo(fieldContainer);

      var clipartListEl;

      var clipartFilterEl = $('<input>')
        .addClass('form-image-clipart-filter')
        .attr('placeholder', 'Find clipart')
        .keydown(function() {
          var $this = $(this);
          setTimeout(function() {
            var val = $this.val().toLowerCase().replace(/[^\w]+/g, '');
            if (!val) {
              clipartListEl.find('img').show();
            } else {
              clipartListEl.find('img').each(function() {
                var $this = $(this);
                $this.toggle($this.attr('title').indexOf(val) >= 0);
              });
            }
          }, 0);
        })
        .appendTo(clipartParamsEl);

      clipartListEl = $('<div>')
        .addClass('form-image-clipart-list')
        .addClass('cancel-parent-scroll')
        .appendTo(clipartParamsEl);

      for (var i = 0; i < studio.forms.ImageField.clipartList_.length; i++) {
        var clipartSrc = 'res/clipart/' + studio.forms.ImageField.clipartList_[i];
        $('<img>')
          .addClass('form-image-clipart-item')
          .attr('src', clipartSrc)
          .attr('title', studio.forms.ImageField.clipartList_[i])
          .click(function(clipartSrc) {
            return function() {
              me.loadClipart_(clipartSrc);
            };
          }(clipartSrc))
          .appendTo(clipartListEl);
      }

      var clipartAttributionEl = $('<div>')
        .addClass('form-image-clipart-attribution')
        .html([
            'For clipart sources, visit ',
            '<a href="http://developer.android.com/design/downloads/">',
                'Android Design: Downloads',
            '</a>.<br>',
            'Additional icons can be found at ',
            '<a href="http://www.androidicons.com">androidicons.com</a>.'
          ].join(''))
        .appendTo(clipartParamsEl);

      typeEls.clipart.click(function(evt) {
        me.setValueType_('clipart');
        if (studio.AUTO_TRIM) {
          me.spaceFormTrimField_.setValue(false);
        }
        me.renderValueAndNotifyChanged_();
      });

      // Prepare UI for the 'text' type
      var textParamsEl = $('<div>')
        .addClass(
          'form-subform ' +
          'form-image-type-params ' +
          'form-image-type-params-text')
        .hide()
        .appendTo(fieldContainer);

      this.textForm_ = new studio.forms.Form(
        this.form_.id_ + '-' + this.id_ + '-textform', {
          onChange: function() {
            var values = me.textForm_.getValues();
            me.textParams_.text = values['text'];
            me.textParams_.fontStack = values['font']
                ? values['font'] : 'Roboto, sans-serif';
            me.valueFilename_ = values['text'];
            me.tryLoadWebFont_();
            me.renderValueAndNotifyChanged_();
          },
          fields: [
            new studio.forms.TextField('text', {
              title: 'Text',
            }),
            new studio.forms.AutocompleteTextField('font', {
              title: 'Font',
              items: studio.forms.ImageField.fontList_
            })
          ]
        });
      this.textForm_.createUI(textParamsEl);

      typeEls.text.click(function(evt) {
        me.setValueType_('text');
        if (studio.AUTO_TRIM) {
          me.spaceFormTrimField_.setValue(true);
        }
        me.renderValueAndNotifyChanged_();
      });
    }

    // Create spacing subform
    if (!this.params_.noTrimForm) {
      this.spaceFormValues_ = {};
      this.spaceForm_ = new studio.forms.Form(
        this.form_.id_ + '-' + this.id_ + '-spaceform', {
          onChange: function() {
            me.spaceFormValues_ = me.spaceForm_.getValues();
            me.renderValueAndNotifyChanged_();
          },
          fields: [
            (this.spaceFormTrimField_ = new studio.forms.BooleanField('trim', {
              title: 'Trim',
              defaultValue: this.params_.defaultValueTrim || false,
              offText: 'Don\'t Trim',
              onText: 'Trim'
            })),
            new studio.forms.RangeField('pad', {
              title: 'Padding',
              defaultValue: 0,
              min: -0.1,
              max: 0.5, // 1/2 of min(width, height)
              step: 0.05,
              textFn: function(v) {
                return (v * 100).toFixed(0) + '%';
              }
            }),
          ]
        });
      this.spaceForm_.createUI($('<div>')
        .addClass('form-subform')
        .appendTo(fieldContainer));
      this.spaceFormValues_ = this.spaceForm_.getValues();
    } else {
      this.spaceFormValues_ = {};
    }

    // Create image preview element
    if (!this.params_.noPreview) {
      this.imagePreview_ = $('<canvas>')
        .addClass('form-image-preview')
        .hide()
        .appendTo(fieldContainer.parent());
    }
  },

  tryLoadWebFont_: function(force) {
    var desiredFont = this.textForm_.getValues()['font'];
    if (this.loadedWebFont_ == desiredFont || !desiredFont) {
      return;
    }

    var me = this;
    if (!force) {
      if (this.tryLoadWebFont_.timeout_) {
        clearTimeout(this.tryLoadWebFont_.timeout_);
      }
      this.tryLoadWebFont_.timeout_ = setTimeout(function() {
        me.tryLoadWebFont_(true);
      }, 500);
      return;
    }

    this.loadedWebFont_ = desiredFont;
    var webFontNodeId = this.form_.id_ + '-' + this.id_ + '-__webfont-stylesheet__';
    $('#' + webFontNodeId).remove();
    $('<link>')
        .attr('id', webFontNodeId)
        .attr('rel', 'stylesheet')
        .attr('href', 'http://fonts.googleapis.com/css?family='
            + encodeURIComponent(desiredFont))
        .bind('load', function() {
          me.renderValueAndNotifyChanged_();
          window.setTimeout(function() {
            me.renderValueAndNotifyChanged_();
          }, 500);
        })
        .appendTo('head');
  },

  setValueType_: function(type) {
    this.valueType_ = type;
    $('input', this.el_).removeAttr('checked');
    $('.form-image-type-params', this.el_.parent()).hide();
    if (type) {
      $('#' + this.getHtmlId() + '-' + type).attr('checked', true);
      $('.form-image-type-params-' + type, this.el_.parent()).show();
    }
  },

  loadClipart_: function(clipartSrc) {
    var useCanvg = USE_CANVG && clipartSrc.match(/\.svg$/);

    $('img.form-image-clipart-item', this.el_.parent()).removeClass('selected');
    $('img[src="' + clipartSrc + '"]').addClass('selected');
    
    this.imageParams_ = {
      canvgSvgUri: useCanvg ? clipartSrc : null,
      uri: useCanvg ? null : clipartSrc
    };
    this.clipartSrc_ = clipartSrc;
    this.valueFilename_ = clipartSrc.match(/[^/]+$/)[0];
    this.renderValueAndNotifyChanged_();
  },

  clearValue: function() {
    this.valueType_ = null;
    this.valueFilename_ = null;
    this.valueCtx_ = null;
    this.fileEl_.val('');
    if (this.imagePreview_) {
      this.imagePreview_.hide();
    }
  },

  getValue: function() {
    return {
      ctx: this.valueCtx_,
      type: this.valueType_,
      name: this.valueFilename_
    };
  },

  // this function is asynchronous
  renderValueAndNotifyChanged_: function() {
    if (!this.valueType_) {
      this.valueCtx_ = null;
    }

    var me = this;

    // Render the base image (text, clipart, or image)
    switch (this.valueType_) {
      case 'image':
      case 'clipart':
        if (this.imageParams_.canvgSvgText || this.imageParams_.canvgSvgUri) {
          var canvas = document.createElement('canvas');
          var size = { w: 800, h: 800 };
          canvas.className = 'offscreen';
          canvas.width = size.w;
          canvas.height = size.h;
          document.body.appendChild(canvas);

          canvg(
            canvas,
            this.imageParams_.canvgSvgText ||
              this.imageParams_.canvgSvgUri,
            {
              scaleWidth: size.w,
              scaleHeight: size.h,
              ignoreMouse: true,
              ignoreAnimation: true,
              ignoreDimensions: true,
              ignoreClear: true
            }
          );
          continue_(canvas.getContext('2d'), size);

          document.body.removeChild(canvas);
        } else if (this.imageParams_.uri) {
          imagelib.loadFromUri(this.imageParams_.uri, function(img) {
            var size = {
              w: img.naturalWidth,
              h: img.naturalHeight
            };
            var ctx = imagelib.drawing.context(size);
            imagelib.drawing.copy(ctx, img, size);
            continue_(ctx, size);
          });
        }
        break;

      case 'text':
        var size = { w: 4800, h: 1600 };
        var textHeight = size.h * 0.75;
        var ctx = imagelib.drawing.context(size);
        var text = this.textParams_.text || '';
        text = ' ' + text + ' ';

        ctx.fillStyle = '#000';
        ctx.font = 'bold ' + textHeight + 'px/' + size.h + 'px ' + this.textParams_.fontStack;
        ctx.textBaseline = 'alphabetic';
        ctx.fillText(text, 0, textHeight);
        size.w = Math.ceil(Math.min(ctx.measureText(text).width, size.w) || size.w);

        continue_(ctx, size);
        break;

      default:
        me.form_.notifyChanged_(me);
    }

    function continue_(srcCtx, srcSize) {
      // Apply trimming
      if (me.spaceFormValues_['trim']) {
        if (me.trimWorker_) {
          me.trimWorker_.terminate();
        }
        me.trimWorker_ = imagelib.drawing.getTrimRect(srcCtx, srcSize, 1,
            function(trimRect) {
              continue2_(srcCtx, srcSize, trimRect);
            });
      } else {
        continue2_(srcCtx, srcSize,
            /*trimRect*/{ x: 0, y: 0, w: srcSize.w, h: srcSize.h });
      }
    }

    function continue2_(srcCtx, srcSize, trimRect) {
      // If trimming, add a tiny bit of padding to fix artifacts around the
      // edges.
      var extraPadding = me.spaceFormValues_['trim'] ? 0.001 : 0;
      if (trimRect.x == 0 && trimRect.y == 0 &&
          trimRect.w == srcSize.w && trimRect.h == srcSize.h) {
        extraPadding = 0;
      }

      var padPx = Math.round(((me.spaceFormValues_['pad'] || 0) + extraPadding) *
                  Math.min(trimRect.w, trimRect.h));
      var targetRect = { x: padPx, y: padPx, w: trimRect.w, h: trimRect.h };

      var outCtx = imagelib.drawing.context({
        w: trimRect.w + padPx * 2,
        h: trimRect.h + padPx * 2
      });

      // TODO: replace with a simple draw() as the centering is useless
      imagelib.drawing.drawCenterInside(outCtx, srcCtx, targetRect, trimRect);

      // Set the final URI value and show a preview
      me.valueCtx_ = outCtx;

      if (me.imagePreview_) {
        me.imagePreview_.attr('width', outCtx.canvas.width);
        me.imagePreview_.attr('height', outCtx.canvas.height);

        var previewCtx = me.imagePreview_.get(0).getContext('2d');
        previewCtx.drawImage(outCtx.canvas, 0, 0);

        me.imagePreview_.show();
      }

      me.form_.notifyChanged_(me);
    }
  },

  serializeValue: function() {
    return {
      type: this.valueType_,
      space: this.spaceForm_.getValuesSerialized(),
      clipart: (this.valueType_ == 'clipart') ? this.clipartSrc_ : null,
      text: (this.valueType_ == 'text') ? this.textForm_.getValuesSerialized()
                                        : null
    };
  },

  deserializeValue: function(o) {
    if (o.type) {
      this.setValueType_(o.type);
    }
    if (o.space) {
      this.spaceForm_.setValuesSerialized(o.space);
      this.spaceFormValues_ = this.spaceForm_.getValues();
    }
    if (o.clipart && this.valueType_ == 'clipart') {
      this.loadClipart_(o.clipart);
    }
    if (o.text && this.valueType_ == 'text') {
      this.textForm_.setValuesSerialized(o.text);
      this.tryLoadWebFont_();
    }
  }
});

studio.forms.ImageField.clipartList_ = [
  'icons/core_overflow.svg',
  'icons/action_about.svg',
  'icons/action_help.svg',
  'icons/action_search.svg',
  'icons/action_settings.svg',
  'icons/alerts_and_states_add_alarm.svg',
  'icons/alerts_and_states_airplane_mode_off.svg',
  'icons/alerts_and_states_airplane_mode_on.svg',
  'icons/alerts_and_states_error.svg',
  'icons/alerts_and_states_warning.svg',
  'icons/av_add_to_queue.svg',
  'icons/av_download.svg',
  'icons/av_fast_forward.svg',
  'icons/av_full_screen.svg',
  'icons/av_make_available_offline.svg',
  'icons/av_next.svg',
  'icons/av_pause.svg',
  'icons/av_pause_over_video.svg',
  'icons/av_play.svg',
  'icons/av_play_over_video.svg',
  'icons/av_previous.svg',
  'icons/av_repeat.svg',
  'icons/av_replay.svg',
  'icons/av_return_from_full_screen.svg',
  'icons/av_rewind.svg',
  'icons/av_shuffle.svg',
  'icons/av_stop.svg',
  'icons/av_upload.svg',
  'icons/collections_cloud.svg',
  'icons/collections_collection.svg',
  'icons/collections_go_to_today.svg',
  'icons/collections_labels.svg',
  'icons/collections_new_label.svg',
  'icons/collections_sort_by_size.svg',
  'icons/collections_view_as_grid.svg',
  'icons/collections_view_as_list.svg',
  'icons/content_attachment.svg',
  'icons/content_backspace.svg',
  'icons/content_copy.svg',
  'icons/content_cut.svg',
  'icons/content_discard.svg',
  'icons/content_edit.svg',
  'icons/content_email.svg',
  'icons/content_event.svg',
  'icons/content_import_export.svg',
  'icons/content_merge.svg',
  'icons/content_new.svg',
  'icons/content_new_attachment.svg',
  'icons/content_new_email.svg',
  'icons/content_new_event.svg',
  'icons/content_new_picture.svg',
  'icons/content_paste.svg',
  'icons/content_picture.svg',
  'icons/content_read.svg',
  'icons/content_remove.svg',
  'icons/content_save.svg',
  'icons/content_select_all.svg',
  'icons/content_split.svg',
  'icons/content_undo.svg',
  'icons/content_unread.svg',
  'icons/device_access_accounts.svg',
  'icons/device_access_alarms.svg',
  'icons/device_access_battery.svg',
  'icons/device_access_bluetooth.svg',
  'icons/device_access_bluetooth_connected.svg',
  'icons/device_access_bluetooth_searching.svg',
  'icons/device_access_brightness_auto.svg',
  'icons/device_access_brightness_high.svg',
  'icons/device_access_brightness_low.svg',
  'icons/device_access_brightness_medium.svg',
  'icons/device_access_call.svg',
  'icons/device_access_camera.svg',
  'icons/device_access_data_usage.svg',
  'icons/device_access_dial_pad.svg',
  'icons/device_access_end_call.svg',
  'icons/device_access_flash_automatic.svg',
  'icons/device_access_flash_off.svg',
  'icons/device_access_flash_on.svg',
  'icons/device_access_location_found.svg',
  'icons/device_access_location_off.svg',
  'icons/device_access_location_searching.svg',
  'icons/device_access_mic.svg',
  'icons/device_access_mic_muted.svg',
  'icons/device_access_network_cell.svg',
  'icons/device_access_network_wifi.svg',
  'icons/device_access_new_account.svg',
  'icons/device_access_not_secure.svg',
  'icons/device_access_ring_volume.svg',
  'icons/device_access_screen_locked_to_landscape.svg',
  'icons/device_access_screen_locked_to_portrait.svg',
  'icons/device_access_screen_rotation.svg',
  'icons/device_access_sd_storage.svg',
  'icons/device_access_secure.svg',
  'icons/device_access_storage_1.svg',
  'icons/device_access_switch_camera.svg',
  'icons/device_access_switch_video.svg',
  'icons/device_access_time.svg',
  'icons/device_access_usb.svg',
  'icons/device_access_video.svg',
  'icons/device_access_volume_muted.svg',
  'icons/device_access_volume_on.svg',
  'icons/hardware_computer.svg',
  'icons/hardware_dock.svg',
  'icons/hardware_gamepad.svg',
  'icons/hardware_headphones.svg',
  'icons/hardware_headset.svg',
  'icons/hardware_keyboard.svg',
  'icons/hardware_mouse.svg',
  'icons/hardware_phone.svg',
  'icons/images_crop.svg',
  'icons/images_rotate_left.svg',
  'icons/images_rotate_right.svg',
  'icons/images_slideshow.svg',
  'icons/location_directions.svg',
  'icons/location_map.svg',
  'icons/location_place.svg',
  'icons/location_web_site.svg',
  'icons/navigation_accept.svg',
  'icons/navigation_back.svg',
  'icons/navigation_cancel.svg',
  'icons/navigation_collapse.svg',
  'icons/navigation_expand.svg',
  'icons/navigation_forward.svg',
  'icons/navigation_next_item.svg',
  'icons/navigation_previous_item.svg',
  'icons/navigation_refresh.svg',
  'icons/rating_bad.svg',
  'icons/rating_favorite.svg',
  'icons/rating_good.svg',
  'icons/rating_half_important.svg',
  'icons/rating_important.svg',
  'icons/rating_not_important.svg',
  'icons/social_add_group.svg',
  'icons/social_add_person.svg',
  'icons/social_cc_bcc.svg',
  'icons/social_chat.svg',
  'icons/social_forward.svg',
  'icons/social_group.svg',
  'icons/social_person.svg',
  'icons/social_reply.svg',
  'icons/social_reply_all.svg',
  'icons/social_send_now.svg',
  'icons/social_share.svg'
];

studio.forms.ImageField.fontList_ = [
  'Roboto',
  'Helvetica',
  'Arial',
  'Georgia',
  'Book Antiqua',
  'Palatino',
  'Courier',
  'Courier New',
  'Webdings',
  'Wingdings'
];

/**
 * Loads the first valid image from a FileList (e.g. drag + drop source), as a data URI. This method
 * will throw an alert() in case of errors and call back with null.
 * @param {FileList} fileList The FileList to load.
 * @param {Function} callback The callback to fire once image loading is done (or fails).
 * @return Returns an object containing 'uri' or 'canvgSvgText' fields representing
 *      the loaded image. There will also be a 'name' field indicating the file name, if one
 *      is available.
 */
studio.forms.ImageField.loadImageFromFileList = function(fileList, callback) {
  fileList = fileList || [];

  var file = null;
  for (var i = 0; i < fileList.length; i++) {
    if (studio.forms.ImageField.isValidFile_(fileList[i])) {
      file = fileList[i];
      break;
    }
  }

  if (!file) {
    alert('Please choose a valid image file (PNG, JPG, GIF, SVG, etc.)');
    callback(null);
    return;
  }

  var useCanvg = USE_CANVG && file.type == 'image/svg+xml';

  var fileReader = new FileReader();

  // Closure to capture the file information.
  fileReader.onload = function(e) {
    callback({
      uri: useCanvg ? null : e.target.result,
      canvgSvgText: useCanvg ? e.target.result : null,
      name: file.name
    });
  };
  fileReader.onerror = function(e) {
    switch(e.target.error.code) {
      case e.target.error.NOT_FOUND_ERR:
        alert('File not found!');
        break;
      case e.target.error.NOT_READABLE_ERR:
        alert('File is not readable');
        break;
      case e.target.error.ABORT_ERR:
        break; // noop
      default:
        alert('An error occurred reading this file.');
    }
    callback(null);
  };
  /*fileReader.onprogress = function(e) {
    $('#read-progress').css('visibility', 'visible');
    // evt is an ProgressEvent.
    if (e.lengthComputable) {
      $('#read-progress').val(Math.round((e.loaded / e.total) * 100));
    } else {
      $('#read-progress').removeAttr('value');
    }
  };*/
  fileReader.onabort = function(e) {
    alert('File read cancelled');
    callback(null);
  };
  /*fileReader.onloadstart = function(e) {
    $('#read-progress').css('visibility', 'visible');
  };*/
  if (useCanvg)
    fileReader.readAsText(file);
  else
    fileReader.readAsDataURL(file);
};

/**
 * Determines whether or not the given File is a valid value for the image.
 * 'File' here is a File using the W3C File API.
 * @private
 * @param {File} file Describe this parameter
 */
studio.forms.ImageField.isValidFile_ = function(file) {
  return !!file.type.toLowerCase().match(/^image\//);
};
/*studio.forms.ImageField.isValidFile_.allowedTypes = {
  'image/png': true,
  'image/jpeg': true,
  'image/svg+xml': true,
  'image/gif': true,
  'image/vnd.adobe.photoshop': true
};*/

studio.forms.ImageField.makeDropHandler_ = function(el, handler) {
  return function(evt) {
    $(el).removeClass('drag-hover');
    handler(evt);
  };
};

studio.forms.ImageField.makeDragoverHandler_ = function(el) {
  return function(evt) {
    el = $(el).get(0);
    if (el._studio_frm_dragtimeout_) {
      window.clearTimeout(el._studio_frm_dragtimeout_);
      el._studio_frm_dragtimeout_ = null;
    }
    evt.dataTransfer.dropEffect = 'link';
    evt.preventDefault();
  };
};

studio.forms.ImageField.makeDragenterHandler_ = function(el) {
  return function(evt) {
    el = $(el).get(0);
    if (el._studio_frm_dragtimeout_) {
      window.clearTimeout(el._studio_frm_dragtimeout_);
      el._studio_frm_dragtimeout_ = null;
    }
    $(el).addClass('drag-hover');
    evt.preventDefault();
  };
};

studio.forms.ImageField.makeDragleaveHandler_ = function(el) {
  return function(evt) {
    el = $(el).get(0);
    if (el._studio_frm_dragtimeout_)
      window.clearTimeout(el._studio_frm_dragtimeout_);
    el._studio_frm_dragtimeout_ = window.setTimeout(function() {
      $(el).removeClass('drag-hover');
    }, 100);
  };
};

// Prevent scrolling for clipart per http://stackoverflow.com/questions/7600454
$(document).ready(function() {
  $('.cancel-parent-scroll').on('mousewheel DOMMouseScroll',
    function(e) {
      var delta = e.originalEvent.wheelDelta || -e.originalEvent.detail;
      this.scrollTop -= delta;
      e.preventDefault();
    });
});

studio.ui = {};

studio.ui.createImageOutputGroup = function(params) {
  return $('<div>')
    .addClass('out-image-group')
    .addClass(params.dark ? 'dark' : 'light')
    .append($('<div>')
      .addClass('label')
      .text(params.label))
    .appendTo(params.container);
};


studio.ui.createImageOutputSlot = function(params) {
  return $('<div>')
    .addClass('out-image-block')
    .append($('<div>')
      .addClass('label')
      .text(params.label))
    .append($('<img>')
      .addClass('out-image')
      .attr('id', params.id))
    .appendTo(params.container);
};


studio.ui.drawImageGuideRects = function(ctx, size, guides) {
  guides = guides || [];

  ctx.save();
  ctx.globalAlpha = 0.5;
  ctx.fillStyle = '#fff';
  ctx.fillRect(0, 0, size.w, size.h);
  ctx.globalAlpha = 1.0;

  var guideColors = studio.ui.drawImageGuideRects.guideColors_;

  for (var i = 0; i < guides.length; i++) {
    ctx.strokeStyle = guideColors[(i - 1) % guideColors.length];
    ctx.strokeRect(guides[i].x + 0.5, guides[i].y + 0.5, guides[i].w - 1, guides[i].h - 1);
  }

  ctx.restore();
};
studio.ui.drawImageGuideRects.guideColors_ = [
  '#f00'
];

studio.ui.setupDragout = function() {
  if (studio.ui.setupDragout.completed_) {
    return;
  }
  studio.ui.setupDragout.completed_ = true;

  $(document).ready(function() {
    document.body.addEventListener('dragstart', function(e) {
      var a = e.target;
      if (a.classList.contains('dragout')) {
        e.dataTransfer.setData('DownloadURL', a.dataset.downloadurl);
      }
    }, false);
  });
};

studio.util = {};

studio.util.getMultBaseMdpi = function(density) {
  switch (density) {
    case 'xxxhdpi': return 4.00;
    case  'xxhdpi': return 3.00;
    case   'xhdpi': return 2.00;
    case    'hdpi': return 1.50;
    case    'mdpi': return 1.00;
    case    'ldpi': return 0.75;
  }
  return 1.0;
};

studio.util.mult = function(s, mult) {
  var d = {};
  for (k in s) {
    d[k] = s[k] * mult;
  }
  return d;
};

studio.util.multRound = function(s, mult) {
  var d = {};
  for (k in s) {
    d[k] = Math.round(s[k] * mult);
  }
  return d;
};

studio.util.sanitizeResourceName = function(s) {
  return s.toLowerCase().replace(/[\s-\.]/g, '_').replace(/[^\w_]/g, '');
};

studio.zip = {};

(function() {
  /**
   * Converts a base64 string to a Blob
   */
  function base64ToBlob_(base64, mimetype) {
    var BASE64_MARKER = ';base64,';
    var raw = window.atob(base64);
    var rawLength = raw.length;
    var uInt8Array = new Uint8Array(rawLength);
    for (var i = 0; i < rawLength; ++i) {
      uInt8Array[i] = raw.charCodeAt(i);
    }

    if (imagelib.util.hasBlobConstructor()) {
      return new Blob([uInt8Array], {type: mimetype})
    }

    var bb = new BlobBuilder();
    bb.append(uInt8Array.buffer);
    return bb.getBlob(mimetype);
  }

  /**
   * Gets the base64 string for the Zip file specified by the
   * zipperHandle (an Asset Studio-specific thing).
   */
  function getZipperBase64Contents(zipperHandle) {
    if (!zipperHandle.fileSpecs_.length)
      return '';

    var zip = new JSZip();
    for (var i = 0; i < zipperHandle.fileSpecs_.length; i++) {
      var fileSpec = zipperHandle.fileSpecs_[i];
      if (fileSpec.base64data)
        zip.add(fileSpec.name, fileSpec.base64data, {base64:true});
      else if (fileSpec.textData)
        zip.add(fileSpec.name, fileSpec.textData);
    }
    return zip.generate();
  }

  window.URL = window.URL || window.webkitURL || window.mozURL;
  window.BlobBuilder = window.BlobBuilder || window.WebKitBlobBuilder ||
                       window.MozBlobBuilder;

  studio.zip.createDownloadifyZipButton = function(element, options) {
    // TODO: badly needs to be documented :-)

    var zipperHandle = {
      fileSpecs_: []
    };

    var link = $('<a>')
        .addClass('dragout')
        .addClass('form-button')
        .attr('disabled', 'disabled')
        .text('Download .ZIP')
        .get(0);

    $(element).replaceWith(link);

    var updateZipTimeout_ = null;

    function updateZip_(now) {
      // this is immediate

      if (link.href) {
        window.URL.revokeObjectURL(link.href);
        link.href = null;
      }

      if (!now) {
        $(link).attr('disabled', 'disabled');

        if (updateZipTimeout_) {
          window.clearTimeout(updateZipTimeout_);
        }

        updateZipTimeout_ = window.setTimeout(function() {
          updateZip_(true);
          updateZipTimeout_ = null;
        }, 500)
        return;
      }

      // this happens on a timeout for throttling

      var filename = zipperHandle.zipFilename_ || 'output.zip';
      if (!zipperHandle.fileSpecs_.length) {
        //alert('No ZIP file data created.');
        return;
      }

      link.download = filename;
      link.href = window.URL.createObjectURL(base64ToBlob_(
          getZipperBase64Contents(zipperHandle),
          'application/zip'));
      link.draggable = true;
      link.dataset.downloadurl = ['application/zip', link.download, link.href].join(':');

      $(link).removeAttr('disabled');
    }

    // Set up zipper control functions
    zipperHandle.setZipFilename = function(zipFilename) {
      zipperHandle.zipFilename_ = zipFilename;
      updateZip_();
    };
    zipperHandle.clear = function() {
      zipperHandle.fileSpecs_ = [];
      updateZip_();
    };
    zipperHandle.add = function(spec) {
      zipperHandle.fileSpecs_.push(spec);
      updateZip_();
    };

    return zipperHandle;
  };

  // Requires the body listener to be set up
  studio.ui.setupDragout();
})();

window.studio = studio;

})();
