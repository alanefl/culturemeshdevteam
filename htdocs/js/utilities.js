//
// Helper functions
//

var cm = cm || {};

/**
 * Adds all missing properties from second obj to first obj
 */
cm.extend = function(first, second){
    for (var prop in second){
        first[prop] = second[prop];
    }
};

/**
 * Searches for a given element in the array, returns -1 if it is not present.
 * @param {Number} [from] The index at which to begin the search
 */
cm.indexOf = function(arr, elt, from){
    if (arr.indexOf) return arr.indexOf(elt, from);

    from = from || 0;
    var len = arr.length;

    if (from < 0) from += len;

    for (; from < len; from++){
        if (from in arr && arr[from] === elt){
            return from;
        }
    }
    return -1;
};

cm.getUniqueId = (function(){
    var id = 0;
    return function(){ return id++; };
})();

//
// Browsers and platforms detection

cm.ie       = function(){ return navigator.userAgent.indexOf('MSIE') != -1; };
cm.safari   = function(){ return navigator.vendor != undefined && navigator.vendor.indexOf("Apple") != -1; };
cm.chrome   = function(){ return navigator.vendor != undefined && navigator.vendor.indexOf('Google') != -1; };
cm.firefox  = function(){ return (navigator.userAgent.indexOf('Mozilla') != -1 && navigator.vendor != undefined && navigator.vendor == ''); };
cm.windows  = function(){ return navigator.platform == "Win32"; };

//
// Events

/** Returns the function which detaches attached event */
cm.attach = function(element, type, fn){
    if (element.addEventListener){
        element.addEventListener(type, fn, false);
    } else if (element.attachEvent){
        element.attachEvent('on' + type, fn);
    }
    return function() {
      cm.detach(element, type, fn)
    }
};
cm.detach = function(element, type, fn){
    if (element.removeEventListener){
        element.removeEventListener(type, fn, false);
    } else if (element.attachEvent){
        element.detachEvent('on' + type, fn);
    }
};

cm.preventDefault = function(e){
    if (e.preventDefault){
        e.preventDefault();
    } else{
        e.returnValue = false;
    }
};

//
// Node manipulations

/**
 * Insert node a before node b.
 */
cm.insertBefore = function(a, b){
    b.parentNode.insertBefore(a, b);
};
cm.remove = function(element){
    element.parentNode.removeChild(element);
};

cm.contains = function(parent, descendant){
    // compareposition returns false in this case
    if (parent == descendant) return true;

    if (parent.contains){
        return parent.contains(descendant);
    } else {
        return !!(descendant.compareDocumentPosition(parent) & 8);
    }
};

/**
 * Creates and returns element from html string
 * Uses innerHTML to create an element
 */
cm.toElement = (function(){
    var div = document.createElement('div');
    return function(html){
        div.innerHTML = html;
        var element = div.firstChild;
        div.removeChild(element);
        return element;
    };
})();

//
// Node properties and attributes

/**
 * Sets styles for an element.
 * Fixes opacity in IE6-8.
 */
cm.css = function(element, styles){
    if (styles.opacity != null && typeof styles.opacity != 'undefined'){
        if (typeof element.style.opacity != 'string' && typeof(element.filters) != 'undefined'){
            styles.filter = 'alpha(opacity=' + Math.round(100 * styles.opacity) + ')';
        }
    }
    cm.extend(element.style, styles);
};
cm.hasClass = function(element, name){
    var re = new RegExp('(^| )' + name + '( |$)');
    return re.test(element.className);
};
cm.addClass = function(element, name){
    if (!cm.hasClass(element, name)){
        element.className += ' ' + name;
    }
};
cm.removeClass = function(element, name){
    var re = new RegExp('(^| )' + name + '( |$)');
    element.className = element.className.replace(re, ' ').replace(/^\s+|\s+$/g, "");
};
cm.setText = function(element, text){
    element.innerText = text;
    element.textContent = text;
};

//
// Selecting elements

cm.children = function(element){
    var children = [],
    child = element.firstChild;

    while (child){
        if (child.nodeType == 1){
            children.push(child);
        }
        child = child.nextSibling;
    }

    return children;
};

cm.getByClass = function(element, className){
    if (element.querySelectorAll){
        return element.querySelectorAll('.' + className);
    }

    var result = [];
    var candidates = element.getElementsByTagName("*");
    var len = candidates.length;

    for (var i = 0; i < len; i++){
        if (cm.hasClass(candidates[i], className)){
            result.push(candidates[i]);
        }
    }
    return result;
};

/**
 * obj2url() takes a json-object as argument and generates
 * a querystring. pretty much like jQuery.param()
 *
 * how to use:
 *
 *    `cm.obj2url({a:'b',c:'d'},'http://any.url/upload?otherParam=value');`
 *
 * will result in:
 *
 *    `http://any.url/upload?otherParam=value&a=b&c=d`
 *
 * @param  Object JSON-Object
 * @param  String current querystring-part
 * @return String encoded querystring
 */
cm.obj2url = function(obj, temp, prefixDone){
    var uristrings = [],
        prefix = '&',
        add = function(nextObj, i){
            var nextTemp = temp
                ? (/\[\]$/.test(temp)) // prevent double-encoding
                   ? temp
                   : temp+'['+i+']'
                : i;
            if ((nextTemp != 'undefined') && (i != 'undefined')) {
                uristrings.push(
                    (typeof nextObj === 'object')
                        ? cm.obj2url(nextObj, nextTemp, true)
                        : (Object.prototype.toString.call(nextObj) === '[object Function]')
                            ? encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj())
                            : encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj)
                );
            }
        };

    if (!prefixDone && temp) {
      prefix = (/\?/.test(temp)) ? (/\?$/.test(temp)) ? '' : '&' : '?';
      uristrings.push(temp);
      uristrings.push(cm.obj2url(obj));
    } else if ((Object.prototype.toString.call(obj) === '[object Array]') && (typeof obj != 'undefined') ) {
        // we wont use a for-in-loop on an array (performance)
        for (var i = 0, len = obj.length; i < len; ++i){
            add(obj[i], i);
        }
    } else if ((typeof obj != 'undefined') && (obj !== null) && (typeof obj === "object")){
        // for anything else but a scalar, we will use for-in-loop
        for (var i in obj){
            add(obj[i], i);
        }
    } else {
        uristrings.push(encodeURIComponent(temp) + '=' + encodeURIComponent(obj));
    }

    return uristrings.join(prefix)
                     .replace(/^&/, '')
                     .replace(/%20/g, '+');
};

/**
 * A generic module which supports object disposing in dispose() method.
 * */
cm.DisposeSupport = {
  _disposers: [],

  /** Run all registered disposers */
  dispose: function() {
    var disposer;
    while (disposer = this._disposers.shift()) {
      disposer();
    }
  },

  /** Add disposer to the collection */
  addDisposer: function(disposeFunction) {
    this._disposers.push(disposeFunction);
  },

  /** Attach event handler and register de-attacher as a disposer */
  _attach: function() {
    this.addDisposer(cm.attach.apply(this, arguments));
  }
};

/*
 * Two buttons and an input used as
 * a simple counter. I'm unsure why this
 * doesn't already exist.
 *
 */
cm.Counter = function(o) {

	// load options
	this._options = {
		element: null,
		left_button: null,
		right_button: null,
		maxCount: 99,
		minCount: -99
	};

	// extend user things
	cm.extend(this._options, o);
	cm.extend(this, cm.DisposeSupport);

	// important elements
	this._element = this._options.element;
	this._left_button = this._createLeftButton(this._options.left_button);
	this._right_button = this._createRightButton(this._options.right_button);

	// stuff
	this._count = 0;
//	this._button = this._createInputButton(this._find(this._element, 'button'));
//	this._errorLabel = this._find(this._element, 'error');
}

cm.Counter.prototype = {
	_increment: function() {

		if (this._count < this._options.maxCount) {
			this._count++;
			$( this._element ).val(this._count);
		}
	},
	_decrement: function() {

		if (this._count > this._options.minCount) {
			this._count--;
			$( this._element ).val(this._count);
		}
	},
	_update: function(o) {
		cm.extend(this._options, o);
	},
	_setValue: function(value) {

		$( this._element ).val(value);
		this._count = value;
	},
	_createLeftButton: function(button) {

		var self = this;

		button.onclick = function(e) {
			
			e.preventDefault();
			self._decrement();
		}

		return button;
	},
	_createRightButton: function(button) {

		var self = this;

		button.onclick = function(e) {
			
			e.preventDefault();
			self._increment();
		}

		return button;
	}
}
