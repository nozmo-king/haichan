var sn=Object.defineProperty;var rn=(t,e,n)=>e in t?sn(t,e,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[e]=n;var _=(t,e,n)=>rn(t,typeof e!="symbol"?e+"":e,n);function gt(t,e){return function(){return t.apply(e,arguments)}}const{toString:on}=Object.prototype,{getPrototypeOf:$e}=Object,{iterator:Te,toStringTag:yt}=Symbol,Ae=(t=>e=>{const n=on.call(e);return t[n]||(t[n]=n.slice(8,-1).toLowerCase())})(Object.create(null)),P=t=>(t=t.toLowerCase(),e=>Ae(e)===t),Me=t=>e=>typeof e===t,{isArray:re}=Array,ce=Me("undefined");function le(t){return t!==null&&!ce(t)&&t.constructor!==null&&!ce(t.constructor)&&M(t.constructor.isBuffer)&&t.constructor.isBuffer(t)}const bt=P("ArrayBuffer");function an(t){let e;return typeof ArrayBuffer<"u"&&ArrayBuffer.isView?e=ArrayBuffer.isView(t):e=t&&t.buffer&&bt(t.buffer),e}const cn=Me("string"),M=Me("function"),wt=Me("number"),ue=t=>t!==null&&typeof t=="object",ln=t=>t===!0||t===!1,ye=t=>{if(Ae(t)!=="object")return!1;const e=$e(t);return(e===null||e===Object.prototype||Object.getPrototypeOf(e)===null)&&!(yt in t)&&!(Te in t)},un=t=>{if(!ue(t)||le(t))return!1;try{return Object.keys(t).length===0&&Object.getPrototypeOf(t)===Object.prototype}catch{return!1}},dn=P("Date"),fn=P("File"),pn=P("Blob"),hn=P("FileList"),mn=t=>ue(t)&&M(t.pipe),gn=t=>{let e;return t&&(typeof FormData=="function"&&t instanceof FormData||M(t.append)&&((e=Ae(t))==="formdata"||e==="object"&&M(t.toString)&&t.toString()==="[object FormData]"))},yn=P("URLSearchParams"),[bn,wn,xn,En]=["ReadableStream","Request","Response","Headers"].map(P),Sn=t=>t.trim?t.trim():t.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,"");function de(t,e,{allOwnKeys:n=!1}={}){if(t===null||typeof t>"u")return;let s,r;if(typeof t!="object"&&(t=[t]),re(t))for(s=0,r=t.length;s<r;s++)e.call(null,t[s],s,t);else{if(le(t))return;const o=n?Object.getOwnPropertyNames(t):Object.keys(t),i=o.length;let a;for(s=0;s<i;s++)a=o[s],e.call(null,t[a],a,t)}}function xt(t,e){if(le(t))return null;e=e.toLowerCase();const n=Object.keys(t);let s=n.length,r;for(;s-- >0;)if(r=n[s],e===r.toLowerCase())return r;return null}const J=typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:global,Et=t=>!ce(t)&&t!==J;function Le(){const{caseless:t}=Et(this)&&this||{},e={},n=(s,r)=>{const o=t&&xt(e,r)||r;ye(e[o])&&ye(s)?e[o]=Le(e[o],s):ye(s)?e[o]=Le({},s):re(s)?e[o]=s.slice():e[o]=s};for(let s=0,r=arguments.length;s<r;s++)arguments[s]&&de(arguments[s],n);return e}const vn=(t,e,n,{allOwnKeys:s}={})=>(de(e,(r,o)=>{n&&M(r)?t[o]=gt(r,n):t[o]=r},{allOwnKeys:s}),t),Rn=t=>(t.charCodeAt(0)===65279&&(t=t.slice(1)),t),Tn=(t,e,n,s)=>{t.prototype=Object.create(e.prototype,s),t.prototype.constructor=t,Object.defineProperty(t,"super",{value:e.prototype}),n&&Object.assign(t.prototype,n)},An=(t,e,n,s)=>{let r,o,i;const a={};if(e=e||{},t==null)return e;do{for(r=Object.getOwnPropertyNames(t),o=r.length;o-- >0;)i=r[o],(!s||s(i,t,e))&&!a[i]&&(e[i]=t[i],a[i]=!0);t=n!==!1&&$e(t)}while(t&&(!n||n(t,e))&&t!==Object.prototype);return e},Mn=(t,e,n)=>{t=String(t),(n===void 0||n>t.length)&&(n=t.length),n-=e.length;const s=t.indexOf(e,n);return s!==-1&&s===n},Bn=t=>{if(!t)return null;if(re(t))return t;let e=t.length;if(!wt(e))return null;const n=new Array(e);for(;e-- >0;)n[e]=t[e];return n},Nn=(t=>e=>t&&e instanceof t)(typeof Uint8Array<"u"&&$e(Uint8Array)),On=(t,e)=>{const s=(t&&t[Te]).call(t);let r;for(;(r=s.next())&&!r.done;){const o=r.value;e.call(t,o[0],o[1])}},Cn=(t,e)=>{let n;const s=[];for(;(n=t.exec(e))!==null;)s.push(n);return s},In=P("HTMLFormElement"),Pn=t=>t.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g,function(n,s,r){return s.toUpperCase()+r}),Ye=(({hasOwnProperty:t})=>(e,n)=>t.call(e,n))(Object.prototype),kn=P("RegExp"),St=(t,e)=>{const n=Object.getOwnPropertyDescriptors(t),s={};de(n,(r,o)=>{let i;(i=e(r,o,t))!==!1&&(s[o]=i||r)}),Object.defineProperties(t,s)},Ln=t=>{St(t,(e,n)=>{if(M(t)&&["arguments","caller","callee"].indexOf(n)!==-1)return!1;const s=t[n];if(M(s)){if(e.enumerable=!1,"writable"in e){e.writable=!1;return}e.set||(e.set=()=>{throw Error("Can not rewrite read-only method '"+n+"'")})}})},Un=(t,e)=>{const n={},s=r=>{r.forEach(o=>{n[o]=!0})};return re(t)?s(t):s(String(t).split(e)),n},Fn=()=>{},zn=(t,e)=>t!=null&&Number.isFinite(t=+t)?t:e;function _n(t){return!!(t&&M(t.append)&&t[yt]==="FormData"&&t[Te])}const Dn=t=>{const e=new Array(10),n=(s,r)=>{if(ue(s)){if(e.indexOf(s)>=0)return;if(le(s))return s;if(!("toJSON"in s)){e[r]=s;const o=re(s)?[]:{};return de(s,(i,a)=>{const d=n(i,r+1);!ce(d)&&(o[a]=d)}),e[r]=void 0,o}}return s};return n(t,0)},Hn=P("AsyncFunction"),jn=t=>t&&(ue(t)||M(t))&&M(t.then)&&M(t.catch),vt=((t,e)=>t?setImmediate:e?((n,s)=>(J.addEventListener("message",({source:r,data:o})=>{r===J&&o===n&&s.length&&s.shift()()},!1),r=>{s.push(r),J.postMessage(n,"*")}))(`axios@${Math.random()}`,[]):n=>setTimeout(n))(typeof setImmediate=="function",M(J.postMessage)),qn=typeof queueMicrotask<"u"?queueMicrotask.bind(J):typeof process<"u"&&process.nextTick||vt,$n=t=>t!=null&&M(t[Te]),c={isArray:re,isArrayBuffer:bt,isBuffer:le,isFormData:gn,isArrayBufferView:an,isString:cn,isNumber:wt,isBoolean:ln,isObject:ue,isPlainObject:ye,isEmptyObject:un,isReadableStream:bn,isRequest:wn,isResponse:xn,isHeaders:En,isUndefined:ce,isDate:dn,isFile:fn,isBlob:pn,isRegExp:kn,isFunction:M,isStream:mn,isURLSearchParams:yn,isTypedArray:Nn,isFileList:hn,forEach:de,merge:Le,extend:vn,trim:Sn,stripBOM:Rn,inherits:Tn,toFlatObject:An,kindOf:Ae,kindOfTest:P,endsWith:Mn,toArray:Bn,forEachEntry:On,matchAll:Cn,isHTMLForm:In,hasOwnProperty:Ye,hasOwnProp:Ye,reduceDescriptors:St,freezeMethods:Ln,toObjectSet:Un,toCamelCase:Pn,noop:Fn,toFiniteNumber:zn,findKey:xt,global:J,isContextDefined:Et,isSpecCompliantForm:_n,toJSONObject:Dn,isAsyncFn:Hn,isThenable:jn,setImmediate:vt,asap:qn,isIterable:$n};function b(t,e,n,s,r){Error.call(this),Error.captureStackTrace?Error.captureStackTrace(this,this.constructor):this.stack=new Error().stack,this.message=t,this.name="AxiosError",e&&(this.code=e),n&&(this.config=n),s&&(this.request=s),r&&(this.response=r,this.status=r.status?r.status:null)}c.inherits(b,Error,{toJSON:function(){return{message:this.message,name:this.name,description:this.description,number:this.number,fileName:this.fileName,lineNumber:this.lineNumber,columnNumber:this.columnNumber,stack:this.stack,config:c.toJSONObject(this.config),code:this.code,status:this.status}}});const Rt=b.prototype,Tt={};["ERR_BAD_OPTION_VALUE","ERR_BAD_OPTION","ECONNABORTED","ETIMEDOUT","ERR_NETWORK","ERR_FR_TOO_MANY_REDIRECTS","ERR_DEPRECATED","ERR_BAD_RESPONSE","ERR_BAD_REQUEST","ERR_CANCELED","ERR_NOT_SUPPORT","ERR_INVALID_URL"].forEach(t=>{Tt[t]={value:t}});Object.defineProperties(b,Tt);Object.defineProperty(Rt,"isAxiosError",{value:!0});b.from=(t,e,n,s,r,o)=>{const i=Object.create(Rt);return c.toFlatObject(t,i,function(d){return d!==Error.prototype},a=>a!=="isAxiosError"),b.call(i,t.message,e,n,s,r),i.cause=t,i.name=t.name,o&&Object.assign(i,o),i};const Vn=null;function Ue(t){return c.isPlainObject(t)||c.isArray(t)}function At(t){return c.endsWith(t,"[]")?t.slice(0,-2):t}function Qe(t,e,n){return t?t.concat(e).map(function(r,o){return r=At(r),!n&&o?"["+r+"]":r}).join(n?".":""):e}function Gn(t){return c.isArray(t)&&!t.some(Ue)}const Jn=c.toFlatObject(c,{},null,function(e){return/^is[A-Z]/.test(e)});function Be(t,e,n){if(!c.isObject(t))throw new TypeError("target must be an object");e=e||new FormData,n=c.toFlatObject(n,{metaTokens:!0,dots:!1,indexes:!1},!1,function(y,h){return!c.isUndefined(h[y])});const s=n.metaTokens,r=n.visitor||l,o=n.dots,i=n.indexes,d=(n.Blob||typeof Blob<"u"&&Blob)&&c.isSpecCompliantForm(e);if(!c.isFunction(r))throw new TypeError("visitor must be a function");function u(p){if(p===null)return"";if(c.isDate(p))return p.toISOString();if(c.isBoolean(p))return p.toString();if(!d&&c.isBlob(p))throw new b("Blob is not supported. Use a Buffer instead.");return c.isArrayBuffer(p)||c.isTypedArray(p)?d&&typeof Blob=="function"?new Blob([p]):Buffer.from(p):p}function l(p,y,h){let E=p;if(p&&!h&&typeof p=="object"){if(c.endsWith(y,"{}"))y=s?y:y.slice(0,-2),p=JSON.stringify(p);else if(c.isArray(p)&&Gn(p)||(c.isFileList(p)||c.endsWith(y,"[]"))&&(E=c.toArray(p)))return y=At(y),E.forEach(function(S,z){!(c.isUndefined(S)||S===null)&&e.append(i===!0?Qe([y],z,o):i===null?y:y+"[]",u(S))}),!1}return Ue(p)?!0:(e.append(Qe(h,y,o),u(p)),!1)}const f=[],m=Object.assign(Jn,{defaultVisitor:l,convertValue:u,isVisitable:Ue});function w(p,y){if(!c.isUndefined(p)){if(f.indexOf(p)!==-1)throw Error("Circular reference detected in "+y.join("."));f.push(p),c.forEach(p,function(E,x){(!(c.isUndefined(E)||E===null)&&r.call(e,E,c.isString(x)?x.trim():x,y,m))===!0&&w(E,y?y.concat(x):[x])}),f.pop()}}if(!c.isObject(t))throw new TypeError("data must be an object");return w(t),e}function et(t){const e={"!":"%21","'":"%27","(":"%28",")":"%29","~":"%7E","%20":"+","%00":"\0"};return encodeURIComponent(t).replace(/[!'()~]|%20|%00/g,function(s){return e[s]})}function Ve(t,e){this._pairs=[],t&&Be(t,this,e)}const Mt=Ve.prototype;Mt.append=function(e,n){this._pairs.push([e,n])};Mt.toString=function(e){const n=e?function(s){return e.call(this,s,et)}:et;return this._pairs.map(function(r){return n(r[0])+"="+n(r[1])},"").join("&")};function Kn(t){return encodeURIComponent(t).replace(/%3A/gi,":").replace(/%24/g,"$").replace(/%2C/gi,",").replace(/%20/g,"+").replace(/%5B/gi,"[").replace(/%5D/gi,"]")}function Bt(t,e,n){if(!e)return t;const s=n&&n.encode||Kn;c.isFunction(n)&&(n={serialize:n});const r=n&&n.serialize;let o;if(r?o=r(e,n):o=c.isURLSearchParams(e)?e.toString():new Ve(e,n).toString(s),o){const i=t.indexOf("#");i!==-1&&(t=t.slice(0,i)),t+=(t.indexOf("?")===-1?"?":"&")+o}return t}class tt{constructor(){this.handlers=[]}use(e,n,s){return this.handlers.push({fulfilled:e,rejected:n,synchronous:s?s.synchronous:!1,runWhen:s?s.runWhen:null}),this.handlers.length-1}eject(e){this.handlers[e]&&(this.handlers[e]=null)}clear(){this.handlers&&(this.handlers=[])}forEach(e){c.forEach(this.handlers,function(s){s!==null&&e(s)})}}const Nt={silentJSONParsing:!0,forcedJSONParsing:!0,clarifyTimeoutError:!1},Wn=typeof URLSearchParams<"u"?URLSearchParams:Ve,Xn=typeof FormData<"u"?FormData:null,Zn=typeof Blob<"u"?Blob:null,Yn={isBrowser:!0,classes:{URLSearchParams:Wn,FormData:Xn,Blob:Zn},protocols:["http","https","file","blob","url","data"]},Ge=typeof window<"u"&&typeof document<"u",Fe=typeof navigator=="object"&&navigator||void 0,Qn=Ge&&(!Fe||["ReactNative","NativeScript","NS"].indexOf(Fe.product)<0),es=typeof WorkerGlobalScope<"u"&&self instanceof WorkerGlobalScope&&typeof self.importScripts=="function",ts=Ge&&window.location.href||"http://localhost",ns=Object.freeze(Object.defineProperty({__proto__:null,hasBrowserEnv:Ge,hasStandardBrowserEnv:Qn,hasStandardBrowserWebWorkerEnv:es,navigator:Fe,origin:ts},Symbol.toStringTag,{value:"Module"})),T={...ns,...Yn};function ss(t,e){return Be(t,new T.classes.URLSearchParams,{visitor:function(n,s,r,o){return T.isNode&&c.isBuffer(n)?(this.append(s,n.toString("base64")),!1):o.defaultVisitor.apply(this,arguments)},...e})}function rs(t){return c.matchAll(/\w+|\[(\w*)]/g,t).map(e=>e[0]==="[]"?"":e[1]||e[0])}function os(t){const e={},n=Object.keys(t);let s;const r=n.length;let o;for(s=0;s<r;s++)o=n[s],e[o]=t[o];return e}function Ot(t){function e(n,s,r,o){let i=n[o++];if(i==="__proto__")return!0;const a=Number.isFinite(+i),d=o>=n.length;return i=!i&&c.isArray(r)?r.length:i,d?(c.hasOwnProp(r,i)?r[i]=[r[i],s]:r[i]=s,!a):((!r[i]||!c.isObject(r[i]))&&(r[i]=[]),e(n,s,r[i],o)&&c.isArray(r[i])&&(r[i]=os(r[i])),!a)}if(c.isFormData(t)&&c.isFunction(t.entries)){const n={};return c.forEachEntry(t,(s,r)=>{e(rs(s),r,n,0)}),n}return null}function is(t,e,n){if(c.isString(t))try{return(e||JSON.parse)(t),c.trim(t)}catch(s){if(s.name!=="SyntaxError")throw s}return(n||JSON.stringify)(t)}const fe={transitional:Nt,adapter:["xhr","http","fetch"],transformRequest:[function(e,n){const s=n.getContentType()||"",r=s.indexOf("application/json")>-1,o=c.isObject(e);if(o&&c.isHTMLForm(e)&&(e=new FormData(e)),c.isFormData(e))return r?JSON.stringify(Ot(e)):e;if(c.isArrayBuffer(e)||c.isBuffer(e)||c.isStream(e)||c.isFile(e)||c.isBlob(e)||c.isReadableStream(e))return e;if(c.isArrayBufferView(e))return e.buffer;if(c.isURLSearchParams(e))return n.setContentType("application/x-www-form-urlencoded;charset=utf-8",!1),e.toString();let a;if(o){if(s.indexOf("application/x-www-form-urlencoded")>-1)return ss(e,this.formSerializer).toString();if((a=c.isFileList(e))||s.indexOf("multipart/form-data")>-1){const d=this.env&&this.env.FormData;return Be(a?{"files[]":e}:e,d&&new d,this.formSerializer)}}return o||r?(n.setContentType("application/json",!1),is(e)):e}],transformResponse:[function(e){const n=this.transitional||fe.transitional,s=n&&n.forcedJSONParsing,r=this.responseType==="json";if(c.isResponse(e)||c.isReadableStream(e))return e;if(e&&c.isString(e)&&(s&&!this.responseType||r)){const i=!(n&&n.silentJSONParsing)&&r;try{return JSON.parse(e)}catch(a){if(i)throw a.name==="SyntaxError"?b.from(a,b.ERR_BAD_RESPONSE,this,null,this.response):a}}return e}],timeout:0,xsrfCookieName:"XSRF-TOKEN",xsrfHeaderName:"X-XSRF-TOKEN",maxContentLength:-1,maxBodyLength:-1,env:{FormData:T.classes.FormData,Blob:T.classes.Blob},validateStatus:function(e){return e>=200&&e<300},headers:{common:{Accept:"application/json, text/plain, */*","Content-Type":void 0}}};c.forEach(["delete","get","head","post","put","patch"],t=>{fe.headers[t]={}});const as=c.toObjectSet(["age","authorization","content-length","content-type","etag","expires","from","host","if-modified-since","if-unmodified-since","last-modified","location","max-forwards","proxy-authorization","referer","retry-after","user-agent"]),cs=t=>{const e={};let n,s,r;return t&&t.split(`
`).forEach(function(i){r=i.indexOf(":"),n=i.substring(0,r).trim().toLowerCase(),s=i.substring(r+1).trim(),!(!n||e[n]&&as[n])&&(n==="set-cookie"?e[n]?e[n].push(s):e[n]=[s]:e[n]=e[n]?e[n]+", "+s:s)}),e},nt=Symbol("internals");function ie(t){return t&&String(t).trim().toLowerCase()}function be(t){return t===!1||t==null?t:c.isArray(t)?t.map(be):String(t)}function ls(t){const e=Object.create(null),n=/([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;let s;for(;s=n.exec(t);)e[s[1]]=s[2];return e}const us=t=>/^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(t.trim());function Ie(t,e,n,s,r){if(c.isFunction(s))return s.call(this,e,n);if(r&&(e=n),!!c.isString(e)){if(c.isString(s))return e.indexOf(s)!==-1;if(c.isRegExp(s))return s.test(e)}}function ds(t){return t.trim().toLowerCase().replace(/([a-z\d])(\w*)/g,(e,n,s)=>n.toUpperCase()+s)}function fs(t,e){const n=c.toCamelCase(" "+e);["get","set","has"].forEach(s=>{Object.defineProperty(t,s+n,{value:function(r,o,i){return this[s].call(this,e,r,o,i)},configurable:!0})})}let B=class{constructor(e){e&&this.set(e)}set(e,n,s){const r=this;function o(a,d,u){const l=ie(d);if(!l)throw new Error("header name must be a non-empty string");const f=c.findKey(r,l);(!f||r[f]===void 0||u===!0||u===void 0&&r[f]!==!1)&&(r[f||d]=be(a))}const i=(a,d)=>c.forEach(a,(u,l)=>o(u,l,d));if(c.isPlainObject(e)||e instanceof this.constructor)i(e,n);else if(c.isString(e)&&(e=e.trim())&&!us(e))i(cs(e),n);else if(c.isObject(e)&&c.isIterable(e)){let a={},d,u;for(const l of e){if(!c.isArray(l))throw TypeError("Object iterator must return a key-value pair");a[u=l[0]]=(d=a[u])?c.isArray(d)?[...d,l[1]]:[d,l[1]]:l[1]}i(a,n)}else e!=null&&o(n,e,s);return this}get(e,n){if(e=ie(e),e){const s=c.findKey(this,e);if(s){const r=this[s];if(!n)return r;if(n===!0)return ls(r);if(c.isFunction(n))return n.call(this,r,s);if(c.isRegExp(n))return n.exec(r);throw new TypeError("parser must be boolean|regexp|function")}}}has(e,n){if(e=ie(e),e){const s=c.findKey(this,e);return!!(s&&this[s]!==void 0&&(!n||Ie(this,this[s],s,n)))}return!1}delete(e,n){const s=this;let r=!1;function o(i){if(i=ie(i),i){const a=c.findKey(s,i);a&&(!n||Ie(s,s[a],a,n))&&(delete s[a],r=!0)}}return c.isArray(e)?e.forEach(o):o(e),r}clear(e){const n=Object.keys(this);let s=n.length,r=!1;for(;s--;){const o=n[s];(!e||Ie(this,this[o],o,e,!0))&&(delete this[o],r=!0)}return r}normalize(e){const n=this,s={};return c.forEach(this,(r,o)=>{const i=c.findKey(s,o);if(i){n[i]=be(r),delete n[o];return}const a=e?ds(o):String(o).trim();a!==o&&delete n[o],n[a]=be(r),s[a]=!0}),this}concat(...e){return this.constructor.concat(this,...e)}toJSON(e){const n=Object.create(null);return c.forEach(this,(s,r)=>{s!=null&&s!==!1&&(n[r]=e&&c.isArray(s)?s.join(", "):s)}),n}[Symbol.iterator](){return Object.entries(this.toJSON())[Symbol.iterator]()}toString(){return Object.entries(this.toJSON()).map(([e,n])=>e+": "+n).join(`
`)}getSetCookie(){return this.get("set-cookie")||[]}get[Symbol.toStringTag](){return"AxiosHeaders"}static from(e){return e instanceof this?e:new this(e)}static concat(e,...n){const s=new this(e);return n.forEach(r=>s.set(r)),s}static accessor(e){const s=(this[nt]=this[nt]={accessors:{}}).accessors,r=this.prototype;function o(i){const a=ie(i);s[a]||(fs(r,i),s[a]=!0)}return c.isArray(e)?e.forEach(o):o(e),this}};B.accessor(["Content-Type","Content-Length","Accept","Accept-Encoding","User-Agent","Authorization"]);c.reduceDescriptors(B.prototype,({value:t},e)=>{let n=e[0].toUpperCase()+e.slice(1);return{get:()=>t,set(s){this[n]=s}}});c.freezeMethods(B);function Pe(t,e){const n=this||fe,s=e||n,r=B.from(s.headers);let o=s.data;return c.forEach(t,function(a){o=a.call(n,o,r.normalize(),e?e.status:void 0)}),r.normalize(),o}function Ct(t){return!!(t&&t.__CANCEL__)}function oe(t,e,n){b.call(this,t??"canceled",b.ERR_CANCELED,e,n),this.name="CanceledError"}c.inherits(oe,b,{__CANCEL__:!0});function It(t,e,n){const s=n.config.validateStatus;!n.status||!s||s(n.status)?t(n):e(new b("Request failed with status code "+n.status,[b.ERR_BAD_REQUEST,b.ERR_BAD_RESPONSE][Math.floor(n.status/100)-4],n.config,n.request,n))}function ps(t){const e=/^([-+\w]{1,25})(:?\/\/|:)/.exec(t);return e&&e[1]||""}function hs(t,e){t=t||10;const n=new Array(t),s=new Array(t);let r=0,o=0,i;return e=e!==void 0?e:1e3,function(d){const u=Date.now(),l=s[o];i||(i=u),n[r]=d,s[r]=u;let f=o,m=0;for(;f!==r;)m+=n[f++],f=f%t;if(r=(r+1)%t,r===o&&(o=(o+1)%t),u-i<e)return;const w=l&&u-l;return w?Math.round(m*1e3/w):void 0}}function ms(t,e){let n=0,s=1e3/e,r,o;const i=(u,l=Date.now())=>{n=l,r=null,o&&(clearTimeout(o),o=null),t(...u)};return[(...u)=>{const l=Date.now(),f=l-n;f>=s?i(u,l):(r=u,o||(o=setTimeout(()=>{o=null,i(r)},s-f)))},()=>r&&i(r)]}const xe=(t,e,n=3)=>{let s=0;const r=hs(50,250);return ms(o=>{const i=o.loaded,a=o.lengthComputable?o.total:void 0,d=i-s,u=r(d),l=i<=a;s=i;const f={loaded:i,total:a,progress:a?i/a:void 0,bytes:d,rate:u||void 0,estimated:u&&a&&l?(a-i)/u:void 0,event:o,lengthComputable:a!=null,[e?"download":"upload"]:!0};t(f)},n)},st=(t,e)=>{const n=t!=null;return[s=>e[0]({lengthComputable:n,total:t,loaded:s}),e[1]]},rt=t=>(...e)=>c.asap(()=>t(...e)),gs=T.hasStandardBrowserEnv?((t,e)=>n=>(n=new URL(n,T.origin),t.protocol===n.protocol&&t.host===n.host&&(e||t.port===n.port)))(new URL(T.origin),T.navigator&&/(msie|trident)/i.test(T.navigator.userAgent)):()=>!0,ys=T.hasStandardBrowserEnv?{write(t,e,n,s,r,o){const i=[t+"="+encodeURIComponent(e)];c.isNumber(n)&&i.push("expires="+new Date(n).toGMTString()),c.isString(s)&&i.push("path="+s),c.isString(r)&&i.push("domain="+r),o===!0&&i.push("secure"),document.cookie=i.join("; ")},read(t){const e=document.cookie.match(new RegExp("(^|;\\s*)("+t+")=([^;]*)"));return e?decodeURIComponent(e[3]):null},remove(t){this.write(t,"",Date.now()-864e5)}}:{write(){},read(){return null},remove(){}};function bs(t){return/^([a-z][a-z\d+\-.]*:)?\/\//i.test(t)}function ws(t,e){return e?t.replace(/\/?\/$/,"")+"/"+e.replace(/^\/+/,""):t}function Pt(t,e,n){let s=!bs(e);return t&&(s||n==!1)?ws(t,e):e}const ot=t=>t instanceof B?{...t}:t;function Y(t,e){e=e||{};const n={};function s(u,l,f,m){return c.isPlainObject(u)&&c.isPlainObject(l)?c.merge.call({caseless:m},u,l):c.isPlainObject(l)?c.merge({},l):c.isArray(l)?l.slice():l}function r(u,l,f,m){if(c.isUndefined(l)){if(!c.isUndefined(u))return s(void 0,u,f,m)}else return s(u,l,f,m)}function o(u,l){if(!c.isUndefined(l))return s(void 0,l)}function i(u,l){if(c.isUndefined(l)){if(!c.isUndefined(u))return s(void 0,u)}else return s(void 0,l)}function a(u,l,f){if(f in e)return s(u,l);if(f in t)return s(void 0,u)}const d={url:o,method:o,data:o,baseURL:i,transformRequest:i,transformResponse:i,paramsSerializer:i,timeout:i,timeoutMessage:i,withCredentials:i,withXSRFToken:i,adapter:i,responseType:i,xsrfCookieName:i,xsrfHeaderName:i,onUploadProgress:i,onDownloadProgress:i,decompress:i,maxContentLength:i,maxBodyLength:i,beforeRedirect:i,transport:i,httpAgent:i,httpsAgent:i,cancelToken:i,socketPath:i,responseEncoding:i,validateStatus:a,headers:(u,l,f)=>r(ot(u),ot(l),f,!0)};return c.forEach(Object.keys({...t,...e}),function(l){const f=d[l]||r,m=f(t[l],e[l],l);c.isUndefined(m)&&f!==a||(n[l]=m)}),n}const kt=t=>{const e=Y({},t);let{data:n,withXSRFToken:s,xsrfHeaderName:r,xsrfCookieName:o,headers:i,auth:a}=e;e.headers=i=B.from(i),e.url=Bt(Pt(e.baseURL,e.url,e.allowAbsoluteUrls),t.params,t.paramsSerializer),a&&i.set("Authorization","Basic "+btoa((a.username||"")+":"+(a.password?unescape(encodeURIComponent(a.password)):"")));let d;if(c.isFormData(n)){if(T.hasStandardBrowserEnv||T.hasStandardBrowserWebWorkerEnv)i.setContentType(void 0);else if((d=i.getContentType())!==!1){const[u,...l]=d?d.split(";").map(f=>f.trim()).filter(Boolean):[];i.setContentType([u||"multipart/form-data",...l].join("; "))}}if(T.hasStandardBrowserEnv&&(s&&c.isFunction(s)&&(s=s(e)),s||s!==!1&&gs(e.url))){const u=r&&o&&ys.read(o);u&&i.set(r,u)}return e},xs=typeof XMLHttpRequest<"u",Es=xs&&function(t){return new Promise(function(n,s){const r=kt(t);let o=r.data;const i=B.from(r.headers).normalize();let{responseType:a,onUploadProgress:d,onDownloadProgress:u}=r,l,f,m,w,p;function y(){w&&w(),p&&p(),r.cancelToken&&r.cancelToken.unsubscribe(l),r.signal&&r.signal.removeEventListener("abort",l)}let h=new XMLHttpRequest;h.open(r.method.toUpperCase(),r.url,!0),h.timeout=r.timeout;function E(){if(!h)return;const S=B.from("getAllResponseHeaders"in h&&h.getAllResponseHeaders()),A={data:!a||a==="text"||a==="json"?h.responseText:h.response,status:h.status,statusText:h.statusText,headers:S,config:t,request:h};It(function(V){n(V),y()},function(V){s(V),y()},A),h=null}"onloadend"in h?h.onloadend=E:h.onreadystatechange=function(){!h||h.readyState!==4||h.status===0&&!(h.responseURL&&h.responseURL.indexOf("file:")===0)||setTimeout(E)},h.onabort=function(){h&&(s(new b("Request aborted",b.ECONNABORTED,t,h)),h=null)},h.onerror=function(){s(new b("Network Error",b.ERR_NETWORK,t,h)),h=null},h.ontimeout=function(){let z=r.timeout?"timeout of "+r.timeout+"ms exceeded":"timeout exceeded";const A=r.transitional||Nt;r.timeoutErrorMessage&&(z=r.timeoutErrorMessage),s(new b(z,A.clarifyTimeoutError?b.ETIMEDOUT:b.ECONNABORTED,t,h)),h=null},o===void 0&&i.setContentType(null),"setRequestHeader"in h&&c.forEach(i.toJSON(),function(z,A){h.setRequestHeader(A,z)}),c.isUndefined(r.withCredentials)||(h.withCredentials=!!r.withCredentials),a&&a!=="json"&&(h.responseType=r.responseType),u&&([m,p]=xe(u,!0),h.addEventListener("progress",m)),d&&h.upload&&([f,w]=xe(d),h.upload.addEventListener("progress",f),h.upload.addEventListener("loadend",w)),(r.cancelToken||r.signal)&&(l=S=>{h&&(s(!S||S.type?new oe(null,t,h):S),h.abort(),h=null)},r.cancelToken&&r.cancelToken.subscribe(l),r.signal&&(r.signal.aborted?l():r.signal.addEventListener("abort",l)));const x=ps(r.url);if(x&&T.protocols.indexOf(x)===-1){s(new b("Unsupported protocol "+x+":",b.ERR_BAD_REQUEST,t));return}h.send(o||null)})},Ss=(t,e)=>{const{length:n}=t=t?t.filter(Boolean):[];if(e||n){let s=new AbortController,r;const o=function(u){if(!r){r=!0,a();const l=u instanceof Error?u:this.reason;s.abort(l instanceof b?l:new oe(l instanceof Error?l.message:l))}};let i=e&&setTimeout(()=>{i=null,o(new b(`timeout ${e} of ms exceeded`,b.ETIMEDOUT))},e);const a=()=>{t&&(i&&clearTimeout(i),i=null,t.forEach(u=>{u.unsubscribe?u.unsubscribe(o):u.removeEventListener("abort",o)}),t=null)};t.forEach(u=>u.addEventListener("abort",o));const{signal:d}=s;return d.unsubscribe=()=>c.asap(a),d}},vs=function*(t,e){let n=t.byteLength;if(n<e){yield t;return}let s=0,r;for(;s<n;)r=s+e,yield t.slice(s,r),s=r},Rs=async function*(t,e){for await(const n of Ts(t))yield*vs(n,e)},Ts=async function*(t){if(t[Symbol.asyncIterator]){yield*t;return}const e=t.getReader();try{for(;;){const{done:n,value:s}=await e.read();if(n)break;yield s}}finally{await e.cancel()}},it=(t,e,n,s)=>{const r=Rs(t,e);let o=0,i,a=d=>{i||(i=!0,s&&s(d))};return new ReadableStream({async pull(d){try{const{done:u,value:l}=await r.next();if(u){a(),d.close();return}let f=l.byteLength;if(n){let m=o+=f;n(m)}d.enqueue(new Uint8Array(l))}catch(u){throw a(u),u}},cancel(d){return a(d),r.return()}},{highWaterMark:2})},Ne=typeof fetch=="function"&&typeof Request=="function"&&typeof Response=="function",Lt=Ne&&typeof ReadableStream=="function",As=Ne&&(typeof TextEncoder=="function"?(t=>e=>t.encode(e))(new TextEncoder):async t=>new Uint8Array(await new Response(t).arrayBuffer())),Ut=(t,...e)=>{try{return!!t(...e)}catch{return!1}},Ms=Lt&&Ut(()=>{let t=!1;const e=new Request(T.origin,{body:new ReadableStream,method:"POST",get duplex(){return t=!0,"half"}}).headers.has("Content-Type");return t&&!e}),at=64*1024,ze=Lt&&Ut(()=>c.isReadableStream(new Response("").body)),Ee={stream:ze&&(t=>t.body)};Ne&&(t=>{["text","arrayBuffer","blob","formData","stream"].forEach(e=>{!Ee[e]&&(Ee[e]=c.isFunction(t[e])?n=>n[e]():(n,s)=>{throw new b(`Response type '${e}' is not supported`,b.ERR_NOT_SUPPORT,s)})})})(new Response);const Bs=async t=>{if(t==null)return 0;if(c.isBlob(t))return t.size;if(c.isSpecCompliantForm(t))return(await new Request(T.origin,{method:"POST",body:t}).arrayBuffer()).byteLength;if(c.isArrayBufferView(t)||c.isArrayBuffer(t))return t.byteLength;if(c.isURLSearchParams(t)&&(t=t+""),c.isString(t))return(await As(t)).byteLength},Ns=async(t,e)=>{const n=c.toFiniteNumber(t.getContentLength());return n??Bs(e)},Os=Ne&&(async t=>{let{url:e,method:n,data:s,signal:r,cancelToken:o,timeout:i,onDownloadProgress:a,onUploadProgress:d,responseType:u,headers:l,withCredentials:f="same-origin",fetchOptions:m}=kt(t);u=u?(u+"").toLowerCase():"text";let w=Ss([r,o&&o.toAbortSignal()],i),p;const y=w&&w.unsubscribe&&(()=>{w.unsubscribe()});let h;try{if(d&&Ms&&n!=="get"&&n!=="head"&&(h=await Ns(l,s))!==0){let A=new Request(e,{method:"POST",body:s,duplex:"half"}),j;if(c.isFormData(s)&&(j=A.headers.get("content-type"))&&l.setContentType(j),A.body){const[V,ge]=st(h,xe(rt(d)));s=it(A.body,at,V,ge)}}c.isString(f)||(f=f?"include":"omit");const E="credentials"in Request.prototype;p=new Request(e,{...m,signal:w,method:n.toUpperCase(),headers:l.normalize().toJSON(),body:s,duplex:"half",credentials:E?f:void 0});let x=await fetch(p,m);const S=ze&&(u==="stream"||u==="response");if(ze&&(a||S&&y)){const A={};["status","statusText","headers"].forEach(Ze=>{A[Ze]=x[Ze]});const j=c.toFiniteNumber(x.headers.get("content-length")),[V,ge]=a&&st(j,xe(rt(a),!0))||[];x=new Response(it(x.body,at,V,()=>{ge&&ge(),y&&y()}),A)}u=u||"text";let z=await Ee[c.findKey(Ee,u)||"text"](x,t);return!S&&y&&y(),await new Promise((A,j)=>{It(A,j,{data:z,headers:B.from(x.headers),status:x.status,statusText:x.statusText,config:t,request:p})})}catch(E){throw y&&y(),E&&E.name==="TypeError"&&/Load failed|fetch/i.test(E.message)?Object.assign(new b("Network Error",b.ERR_NETWORK,t,p),{cause:E.cause||E}):b.from(E,E&&E.code,t,p)}}),_e={http:Vn,xhr:Es,fetch:Os};c.forEach(_e,(t,e)=>{if(t){try{Object.defineProperty(t,"name",{value:e})}catch{}Object.defineProperty(t,"adapterName",{value:e})}});const ct=t=>`- ${t}`,Cs=t=>c.isFunction(t)||t===null||t===!1,Ft={getAdapter:t=>{t=c.isArray(t)?t:[t];const{length:e}=t;let n,s;const r={};for(let o=0;o<e;o++){n=t[o];let i;if(s=n,!Cs(n)&&(s=_e[(i=String(n)).toLowerCase()],s===void 0))throw new b(`Unknown adapter '${i}'`);if(s)break;r[i||"#"+o]=s}if(!s){const o=Object.entries(r).map(([a,d])=>`adapter ${a} `+(d===!1?"is not supported by the environment":"is not available in the build"));let i=e?o.length>1?`since :
`+o.map(ct).join(`
`):" "+ct(o[0]):"as no adapter specified";throw new b("There is no suitable adapter to dispatch the request "+i,"ERR_NOT_SUPPORT")}return s},adapters:_e};function ke(t){if(t.cancelToken&&t.cancelToken.throwIfRequested(),t.signal&&t.signal.aborted)throw new oe(null,t)}function lt(t){return ke(t),t.headers=B.from(t.headers),t.data=Pe.call(t,t.transformRequest),["post","put","patch"].indexOf(t.method)!==-1&&t.headers.setContentType("application/x-www-form-urlencoded",!1),Ft.getAdapter(t.adapter||fe.adapter)(t).then(function(s){return ke(t),s.data=Pe.call(t,t.transformResponse,s),s.headers=B.from(s.headers),s},function(s){return Ct(s)||(ke(t),s&&s.response&&(s.response.data=Pe.call(t,t.transformResponse,s.response),s.response.headers=B.from(s.response.headers))),Promise.reject(s)})}const zt="1.11.0",Oe={};["object","boolean","number","function","string","symbol"].forEach((t,e)=>{Oe[t]=function(s){return typeof s===t||"a"+(e<1?"n ":" ")+t}});const ut={};Oe.transitional=function(e,n,s){function r(o,i){return"[Axios v"+zt+"] Transitional option '"+o+"'"+i+(s?". "+s:"")}return(o,i,a)=>{if(e===!1)throw new b(r(i," has been removed"+(n?" in "+n:"")),b.ERR_DEPRECATED);return n&&!ut[i]&&(ut[i]=!0,console.warn(r(i," has been deprecated since v"+n+" and will be removed in the near future"))),e?e(o,i,a):!0}};Oe.spelling=function(e){return(n,s)=>(console.warn(`${s} is likely a misspelling of ${e}`),!0)};function Is(t,e,n){if(typeof t!="object")throw new b("options must be an object",b.ERR_BAD_OPTION_VALUE);const s=Object.keys(t);let r=s.length;for(;r-- >0;){const o=s[r],i=e[o];if(i){const a=t[o],d=a===void 0||i(a,o,t);if(d!==!0)throw new b("option "+o+" must be "+d,b.ERR_BAD_OPTION_VALUE);continue}if(n!==!0)throw new b("Unknown option "+o,b.ERR_BAD_OPTION)}}const we={assertOptions:Is,validators:Oe},k=we.validators;let K=class{constructor(e){this.defaults=e||{},this.interceptors={request:new tt,response:new tt}}async request(e,n){try{return await this._request(e,n)}catch(s){if(s instanceof Error){let r={};Error.captureStackTrace?Error.captureStackTrace(r):r=new Error;const o=r.stack?r.stack.replace(/^.+\n/,""):"";try{s.stack?o&&!String(s.stack).endsWith(o.replace(/^.+\n.+\n/,""))&&(s.stack+=`
`+o):s.stack=o}catch{}}throw s}}_request(e,n){typeof e=="string"?(n=n||{},n.url=e):n=e||{},n=Y(this.defaults,n);const{transitional:s,paramsSerializer:r,headers:o}=n;s!==void 0&&we.assertOptions(s,{silentJSONParsing:k.transitional(k.boolean),forcedJSONParsing:k.transitional(k.boolean),clarifyTimeoutError:k.transitional(k.boolean)},!1),r!=null&&(c.isFunction(r)?n.paramsSerializer={serialize:r}:we.assertOptions(r,{encode:k.function,serialize:k.function},!0)),n.allowAbsoluteUrls!==void 0||(this.defaults.allowAbsoluteUrls!==void 0?n.allowAbsoluteUrls=this.defaults.allowAbsoluteUrls:n.allowAbsoluteUrls=!0),we.assertOptions(n,{baseUrl:k.spelling("baseURL"),withXsrfToken:k.spelling("withXSRFToken")},!0),n.method=(n.method||this.defaults.method||"get").toLowerCase();let i=o&&c.merge(o.common,o[n.method]);o&&c.forEach(["delete","get","head","post","put","patch","common"],p=>{delete o[p]}),n.headers=B.concat(i,o);const a=[];let d=!0;this.interceptors.request.forEach(function(y){typeof y.runWhen=="function"&&y.runWhen(n)===!1||(d=d&&y.synchronous,a.unshift(y.fulfilled,y.rejected))});const u=[];this.interceptors.response.forEach(function(y){u.push(y.fulfilled,y.rejected)});let l,f=0,m;if(!d){const p=[lt.bind(this),void 0];for(p.unshift(...a),p.push(...u),m=p.length,l=Promise.resolve(n);f<m;)l=l.then(p[f++],p[f++]);return l}m=a.length;let w=n;for(f=0;f<m;){const p=a[f++],y=a[f++];try{w=p(w)}catch(h){y.call(this,h);break}}try{l=lt.call(this,w)}catch(p){return Promise.reject(p)}for(f=0,m=u.length;f<m;)l=l.then(u[f++],u[f++]);return l}getUri(e){e=Y(this.defaults,e);const n=Pt(e.baseURL,e.url,e.allowAbsoluteUrls);return Bt(n,e.params,e.paramsSerializer)}};c.forEach(["delete","get","head","options"],function(e){K.prototype[e]=function(n,s){return this.request(Y(s||{},{method:e,url:n,data:(s||{}).data}))}});c.forEach(["post","put","patch"],function(e){function n(s){return function(o,i,a){return this.request(Y(a||{},{method:e,headers:s?{"Content-Type":"multipart/form-data"}:{},url:o,data:i}))}}K.prototype[e]=n(),K.prototype[e+"Form"]=n(!0)});let Ps=class _t{constructor(e){if(typeof e!="function")throw new TypeError("executor must be a function.");let n;this.promise=new Promise(function(o){n=o});const s=this;this.promise.then(r=>{if(!s._listeners)return;let o=s._listeners.length;for(;o-- >0;)s._listeners[o](r);s._listeners=null}),this.promise.then=r=>{let o;const i=new Promise(a=>{s.subscribe(a),o=a}).then(r);return i.cancel=function(){s.unsubscribe(o)},i},e(function(o,i,a){s.reason||(s.reason=new oe(o,i,a),n(s.reason))})}throwIfRequested(){if(this.reason)throw this.reason}subscribe(e){if(this.reason){e(this.reason);return}this._listeners?this._listeners.push(e):this._listeners=[e]}unsubscribe(e){if(!this._listeners)return;const n=this._listeners.indexOf(e);n!==-1&&this._listeners.splice(n,1)}toAbortSignal(){const e=new AbortController,n=s=>{e.abort(s)};return this.subscribe(n),e.signal.unsubscribe=()=>this.unsubscribe(n),e.signal}static source(){let e;return{token:new _t(function(r){e=r}),cancel:e}}};function ks(t){return function(n){return t.apply(null,n)}}function Ls(t){return c.isObject(t)&&t.isAxiosError===!0}const De={Continue:100,SwitchingProtocols:101,Processing:102,EarlyHints:103,Ok:200,Created:201,Accepted:202,NonAuthoritativeInformation:203,NoContent:204,ResetContent:205,PartialContent:206,MultiStatus:207,AlreadyReported:208,ImUsed:226,MultipleChoices:300,MovedPermanently:301,Found:302,SeeOther:303,NotModified:304,UseProxy:305,Unused:306,TemporaryRedirect:307,PermanentRedirect:308,BadRequest:400,Unauthorized:401,PaymentRequired:402,Forbidden:403,NotFound:404,MethodNotAllowed:405,NotAcceptable:406,ProxyAuthenticationRequired:407,RequestTimeout:408,Conflict:409,Gone:410,LengthRequired:411,PreconditionFailed:412,PayloadTooLarge:413,UriTooLong:414,UnsupportedMediaType:415,RangeNotSatisfiable:416,ExpectationFailed:417,ImATeapot:418,MisdirectedRequest:421,UnprocessableEntity:422,Locked:423,FailedDependency:424,TooEarly:425,UpgradeRequired:426,PreconditionRequired:428,TooManyRequests:429,RequestHeaderFieldsTooLarge:431,UnavailableForLegalReasons:451,InternalServerError:500,NotImplemented:501,BadGateway:502,ServiceUnavailable:503,GatewayTimeout:504,HttpVersionNotSupported:505,VariantAlsoNegotiates:506,InsufficientStorage:507,LoopDetected:508,NotExtended:510,NetworkAuthenticationRequired:511};Object.entries(De).forEach(([t,e])=>{De[e]=t});function Dt(t){const e=new K(t),n=gt(K.prototype.request,e);return c.extend(n,K.prototype,e,{allOwnKeys:!0}),c.extend(n,e,null,{allOwnKeys:!0}),n.create=function(r){return Dt(Y(t,r))},n}const R=Dt(fe);R.Axios=K;R.CanceledError=oe;R.CancelToken=Ps;R.isCancel=Ct;R.VERSION=zt;R.toFormData=Be;R.AxiosError=b;R.Cancel=R.CanceledError;R.all=function(e){return Promise.all(e)};R.spread=ks;R.isAxiosError=Ls;R.mergeConfig=Y;R.AxiosHeaders=B;R.formToJSON=t=>Ot(c.isHTMLForm(t)?new FormData(t):t);R.getAdapter=Ft.getAdapter;R.HttpStatusCode=De;R.default=R;const{Axios:ir,AxiosError:ar,CanceledError:cr,isCancel:lr,CancelToken:ur,VERSION:dr,all:fr,Cancel:pr,isAxiosError:hr,spread:mr,toFormData:gr,AxiosHeaders:yr,HttpStatusCode:br,formToJSON:wr,getAdapter:xr,mergeConfig:Er}=R;window.axios=R;window.axios.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";/*! noble-secp256k1 - MIT License (c) 2019 Paul Miller (paulmillr.com) */const Ht={p:0xfffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2fn,n:0xfffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141n,h:1n,a:0n,b:7n,Gx:0x79be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798n,Gy:0x483ada7726a3c4655da4fbfc0e1108a8fd17b448a68554199c47d08ffb10d4b8n},{p:W,n:F,Gx:Us,Gy:Fs,b:jt}=Ht,N=32,X=64,v=(t="")=>{throw new Error(t)},qt=t=>typeof t=="bigint",$t=t=>typeof t=="string",zs=t=>t instanceof Uint8Array||ArrayBuffer.isView(t)&&t.constructor.name==="Uint8Array",Q=(t,e)=>!zs(t)||typeof e=="number"&&e>0&&t.length!==e?v("Uint8Array expected"):t,Z=t=>new Uint8Array(t),_s=t=>Uint8Array.from(t),Vt=(t,e)=>t.toString(16).padStart(e,"0"),Ce=t=>Array.from(Q(t)).map(e=>Vt(e,2)).join(""),D={_0:48,_9:57,A:65,F:70,a:97,f:102},dt=t=>{if(t>=D._0&&t<=D._9)return t-D._0;if(t>=D.A&&t<=D.F)return t-(D.A-10);if(t>=D.a&&t<=D.f)return t-(D.a-10)},Je=t=>{const e="hex invalid";if(!$t(t))return v(e);const n=t.length,s=n/2;if(n%2)return v(e);const r=Z(s);for(let o=0,i=0;o<s;o++,i+=2){const a=dt(t.charCodeAt(i)),d=dt(t.charCodeAt(i+1));if(a===void 0||d===void 0)return v(e);r[o]=a*16+d}return r},I=(t,e)=>Q($t(t)?Je(t):_s(Q(t)),e),Gt=()=>globalThis==null?void 0:globalThis.crypto,Ds=()=>{var t;return((t=Gt())==null?void 0:t.subtle)??v("crypto.subtle must be defined")},ee=(...t)=>{const e=Z(t.reduce((s,r)=>s+Q(r).length,0));let n=0;return t.forEach(s=>{e.set(s,n),n+=s.length}),e},Ke=(t=N)=>Gt().getRandomValues(Z(t)),ne=BigInt,pe=(t,e,n,s="bad number: out of range")=>qt(t)&&e<=t&&t<n?t:v(s),g=(t,e=W)=>{const n=t%e;return n>=0n?n:e+n},O=t=>g(t,F),he=(t,e)=>{(t===0n||e<=0n)&&v("no inverse n="+t+" mod="+e);let n=g(t,e),s=e,r=0n,o=1n;for(;n!==0n;){const i=s/n,a=s%n,d=r-o*i;s=n,n=a,r=o,o=d}return s===1n?g(r,e):v("no inverse")},Hs=t=>{const e=q[t];return typeof e!="function"&&v("hashes."+t+" not set"),e},ft=t=>t instanceof C?t:v("Point expected"),Jt=t=>g(g(t*t)*t+jt),pt=t=>pe(t,0n,W),ae=t=>pe(t,1n,W),He=t=>pe(t,1n,F),je=t=>(t&1n)===0n,te=t=>Uint8Array.of(t),Kt=t=>te(je(t)?2:3),js=t=>{const e=Jt(ae(t));let n=1n;for(let s=e,r=(W+1n)/4n;r>0n;r>>=1n)r&1n&&(n=n*s%W),s=s*s%W;return g(n*n)===e?n:v("sqrt invalid")},L=class L{constructor(e,n,s){_(this,"px");_(this,"py");_(this,"pz");this.px=pt(e),this.py=ae(n),this.pz=pt(s),Object.freeze(this)}static fromBytes(e){Q(e);let n;const s=e[0],r=e.subarray(1),o=Se(r,0,N),i=e.length;if(i===N+1&&[2,3].includes(s)){let a=js(o);const d=je(a);je(ne(s))!==d&&(a=g(-a)),n=new L(o,a,1n)}return i===X+1&&s===4&&(n=new L(o,Se(r,N,X),1n)),n?n.assertValidity():v("bad point: not on curve")}equals(e){const{px:n,py:s,pz:r}=this,{px:o,py:i,pz:a}=ft(e),d=g(n*a),u=g(o*r),l=g(s*a),f=g(i*r);return d===u&&l===f}is0(){return this.equals(G)}negate(){return new L(this.px,g(-this.py),this.pz)}double(){return this.add(this)}add(e){const{px:n,py:s,pz:r}=this,{px:o,py:i,pz:a}=ft(e),d=0n,u=jt;let l=0n,f=0n,m=0n;const w=g(u*3n);let p=g(n*o),y=g(s*i),h=g(r*a),E=g(n+s),x=g(o+i);E=g(E*x),x=g(p+y),E=g(E-x),x=g(n+r);let S=g(o+a);return x=g(x*S),S=g(p+h),x=g(x-S),S=g(s+r),l=g(i+a),S=g(S*l),l=g(y+h),S=g(S-l),m=g(d*x),l=g(w*h),m=g(l+m),l=g(y-m),m=g(y+m),f=g(l*m),y=g(p+p),y=g(y+p),h=g(d*h),x=g(w*x),y=g(y+h),h=g(p-h),h=g(d*h),x=g(x+h),p=g(y*x),f=g(f+p),p=g(S*x),l=g(E*l),l=g(l-p),p=g(E*y),m=g(S*m),m=g(m+p),new L(l,f,m)}multiply(e,n=!0){if(!n&&e===0n)return G;if(He(e),e===1n)return this;if(this.equals(U))return er(e).p;let s=G,r=U;for(let o=this;e>0n;o=o.double(),e>>=1n)e&1n?s=s.add(o):n&&(r=r.add(o));return s}toAffine(){const{px:e,py:n,pz:s}=this;if(this.equals(G))return{x:0n,y:0n};if(s===1n)return{x:e,y:n};const r=he(s,W);return g(s*r)!==1n&&v("inverse invalid"),{x:g(e*r),y:g(n*r)}}assertValidity(){const{x:e,y:n}=this.toAffine();return ae(e),ae(n),g(n*n)===Jt(e)?this:v("bad point: not on curve")}toBytes(e=!0){const{x:n,y:s}=this.assertValidity().toAffine(),r=$(n);return e?ee(Kt(s),r):ee(te(4),r,$(s))}static fromAffine(e){const{x:n,y:s}=e;return n===0n&&s===0n?G:new L(n,s,1n)}toHex(e){return Ce(this.toBytes(e))}static fromPrivateKey(e){return U.multiply(se(e))}static fromHex(e){return L.fromBytes(I(e))}get x(){return this.toAffine().x}get y(){return this.toAffine().y}toRawBytes(e){return this.toBytes(e)}};_(L,"BASE"),_(L,"ZERO");let C=L;const U=new C(Us,Fs,1n),G=new C(0n,1n,0n);C.BASE=U;C.ZERO=G;const Wt=(t,e,n)=>U.multiply(e,!1).add(t.multiply(n,!1)).assertValidity(),me=t=>ne("0x"+(Ce(t)||"0")),Se=(t,e,n)=>me(t.subarray(e,n)),qs=2n**256n,$=t=>Je(Vt(pe(t,0n,qs),X)),se=t=>{const e=qt(t)?t:me(I(t,N));return pe(e,1n,F,"private key invalid 3")},ve=t=>t>F>>1n,Xt=(t,e=!0)=>U.multiply(se(t)).toBytes(e);class H{constructor(e,n,s){_(this,"r");_(this,"s");_(this,"recovery");this.r=He(e),this.s=He(n),s!=null&&(this.recovery=s),Object.freeze(this)}static fromBytes(e){Q(e,X);const n=Se(e,0,N),s=Se(e,N,X);return new H(n,s)}toBytes(){const{r:e,s:n}=this;return ee($(e),$(n))}addRecoveryBit(e){return new H(this.r,this.s,e)}hasHighS(){return ve(this.s)}toCompactRawBytes(){return this.toBytes()}toCompactHex(){return Ce(this.toBytes())}recoverPublicKey(e){return Js(this,e)}static fromCompact(e){return H.fromBytes(I(e,X))}assertValidity(){return this}normalizeS(){const{r:e,s:n,recovery:s}=this;return ve(n)?new H(e,O(-n),s):this}}const Zt=t=>{const e=t.length*8-256;e>1024&&v("msg invalid");const n=me(t);return e>0?n>>ne(e):n},We=t=>O(Zt(Q(t))),Xe={lowS:!0},$s={lowS:!0},Yt=(t,e,n=Xe)=>{["der","recovered","canonical"].some(m=>m in n)&&v("option not supported");let{lowS:s,extraEntropy:r}=n;s==null&&(s=!0);const o=$,i=We(I(t)),a=o(i),d=se(e),u=[o(d),a];r&&u.push(r===!0?Ke(N):I(r));const l=i,f=m=>{const w=Zt(m);if(!(1n<=w&&w<F))return;const p=U.multiply(w).toAffine(),y=O(p.x);if(y===0n)return;const h=he(w,F),E=O(h*O(l+O(d*y)));if(E===0n)return;let x=E,S=(p.x===y?0:2)|Number(p.y&1n);return s&&ve(E)&&(x=O(-E),S^=1),new H(y,x,S)};return{seed:ee(...u),k2sig:f}},Qt=t=>{let e=Z(N),n=Z(N),s=0;const r=Z(0),o=()=>{e.fill(1),n.fill(0),s=0},i=1e3,a="drbg: tried 1000 values";if(t){const d=(...f)=>q.hmacSha256Async(n,e,...f),u=async(f=r)=>{n=await d(te(0),f),e=await d(),f.length!==0&&(n=await d(te(1),f),e=await d())},l=async()=>(s++>=i&&v(a),e=await d(),e);return async(f,m)=>{o(),await u(f);let w;for(;!(w=m(await l()));)await u();return o(),w}}else{const d=(...f)=>Hs("hmacSha256Sync")(n,e,...f),u=(f=r)=>{n=d(te(0),f),e=d(),f.length!==0&&(n=d(te(1),f),e=d())},l=()=>(s++>=i&&v(a),e=d(),e);return(f,m)=>{o(),u(f);let w;for(;!(w=m(l()));)u();return o(),w}}},Vs=async(t,e,n=Xe)=>{const{seed:s,k2sig:r}=Yt(t,e,n);return await Qt(!0)(s,r)},en=(t,e,n=Xe)=>{const{seed:s,k2sig:r}=Yt(t,e,n);return Qt(!1)(s,r)},Gs=(t,e,n,s=$s)=>{let{lowS:r}=s;r==null&&(r=!0),"strict"in s&&v("option not supported");let o;const i=t&&typeof t=="object"&&"r"in t;!i&&I(t).length!==X&&v("signature must be 64 bytes");try{o=i?new H(t.r,t.s):H.fromCompact(t);const a=We(I(e)),d=C.fromBytes(I(n)),{r:u,s:l}=o;if(r&&ve(l))return!1;const f=he(l,F),m=O(a*f),w=O(u*f),p=Wt(d,m,w).toAffine();return O(p.x)===u}catch{return!1}},Js=(t,e)=>{const{r:n,s,recovery:r}=t;[0,1,2,3].includes(r)||v("recovery id invalid");const o=We(I(e,N)),i=r===2||r===3?n+F:n;ae(i);const a=Kt(ne(r)),d=ee(a,$(i)),u=C.fromBytes(d),l=he(i,F),f=O(-o*l),m=O(s*l);return Wt(u,f,m)},Ks=(t,e,n=!0)=>C.fromBytes(I(e)).multiply(se(t)).toBytes(n),tn=t=>{t=I(t),(t.length<N+8||t.length>1024)&&v("expected 40-1024b");const e=g(me(t),F-1n);return $(e+1n)},Ws=()=>tn(Ke(N+16)),Xs="SHA-256",q={hexToBytes:Je,bytesToHex:Ce,concatBytes:ee,bytesToNumberBE:me,numberToBytesBE:$,mod:g,invert:he,hmacSha256Async:async(t,...e)=>{const n=Ds(),s="HMAC",r=await n.importKey("raw",t,{name:s,hash:{name:Xs}},!1,["sign"]);return Z(await n.sign(s,r,ee(...e)))},hmacSha256Sync:void 0,hashToPrivateKey:tn,randomBytes:Ke},Zs={normPrivateKeyToScalar:se,isValidPrivateKey:t=>{try{return!!se(t)}catch{return!1}},randomPrivateKey:Ws,precompute:(t=8,e=U)=>(e.multiply(3n),e)},Re=8,Ys=256,nn=Math.ceil(Ys/Re)+1,qe=2**(Re-1),Qs=()=>{const t=[];let e=U,n=e;for(let s=0;s<nn;s++){n=e,t.push(n);for(let r=1;r<qe;r++)n=n.add(e),t.push(n);e=n.double()}return t};let ht;const mt=(t,e)=>{const n=e.negate();return t?n:e},er=t=>{const e=ht||(ht=Qs());let n=G,s=U;const r=2**Re,o=r,i=ne(r-1),a=ne(Re);for(let d=0;d<nn;d++){let u=Number(t&i);t>>=a,u>qe&&(u-=o,t+=1n);const l=d*qe,f=l,m=l+Math.abs(u)-1,w=d%2!==0,p=u<0;u===0?s=s.add(mt(w,e[f])):n=n.add(mt(p,e[m]))}return{p:n,f:s}},tr=Object.freeze(Object.defineProperty({__proto__:null,CURVE:Ht,Point:C,ProjectivePoint:C,Signature:H,etc:q,getPublicKey:Xt,getSharedSecret:Ks,sign:en,signAsync:Vs,utils:Zs,verify:Gs},Symbol.toStringTag,{value:"Module"}));q.hmacSha256Sync=(t,...e)=>{const n=new TextEncoder,s=t instanceof Uint8Array?t:n.encode(t),r=e.reduce((i,a)=>{const d=a instanceof Uint8Array?a:n.encode(a),u=new Uint8Array(i.length+d.length);return u.set(i),u.set(d,i.length),u},s),o=new Uint8Array(32);for(let i=0;i<o.length;i++)o[i]=r[i%r.length]^i*7;return o};console.log("secp256k1 module:",tr);console.log("secp256k1.etc:",q);window.authModule={async authenticate(t,e){if(!/^[a-fA-F0-9]{64}$/.test(t))throw new Error("Private key must be 64 hexadecimal characters");try{const n=q.hexToBytes(t),s=Xt(n,!0),r=q.bytesToHex(s),o=await Promise.race([fetch("/challenge",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":e},body:JSON.stringify({public_key:r})}),new Promise((l,f)=>setTimeout(()=>f(new Error("Challenge request timed out")),15e3))]);if(!o.ok){let l="Failed to get challenge";try{const f=await o.json();l=f.error||f.message||l}catch{l=await o.text()||l}throw o.status===403||l.includes("not authorized")?new Error("Public key not authorized. Please register first with a friend code or use an existing authorized key."):new Error(l)}const i=await o.json(),a=await crypto.subtle.digest("SHA-256",new TextEncoder().encode(i.challenge)),d=await en(new Uint8Array(a),n),u=q.bytesToHex(d.toCompactRawBytes());return{user_id:i.user_id,challenge:i.challenge,signature:u}}catch(n){throw console.error("Authentication error:",n),n}},submitLogin(t,e){try{console.log("Submitting login form with data:",t);const n=document.createElement("form");n.method="POST",n.action="/login";const s={_token:e,user_id:t.user_id,challenge:t.challenge,signature:t.signature};Object.entries(s).forEach(([r,o])=>{const i=document.createElement("input");i.type="hidden",i.name=r,i.value=o,n.appendChild(i)}),document.body.appendChild(n),setTimeout(()=>{if(document.body.contains(n))throw console.error("Form submission appears to have failed - removing form"),n.remove(),new Error("Login form submission timed out")},1e4),n.submit()}catch(n){throw console.error("Error submitting login form:",n),n}}};console.log("authModule set on window:",window.authModule);window.authModuleLoaded=!0;document.dispatchEvent(new CustomEvent("authModuleReady"));class nr{constructor(){this.isInitialized=!1,this.isActive=!0,this.state={power:5,mode:"mouseover",currentTarget:null,isMinimized:!1,sessionStats:{startTime:Date.now(),totalHashes:0,totalProofs:0,totalPoints:0,currentHashrate:0},activeTargets:new Map,performance:{avgHashTime:0,cpuUsage:0,memoryUsage:0}},this.configs={patterns:{21:{points:.1,difficulty:"Trivial"},"21e":{points:.5,difficulty:"Easy"},"21e8":{points:100,difficulty:"Standard"},"21e80":{points:500,difficulty:"Hard"},"21e800":{points:2500,difficulty:"Very Hard"},"21e8000":{points:1e4,difficulty:"Extreme"}},rare:{deadbeef:{points:5e3,rarity:"🏆 LEGENDARY"},1337:{points:2500,rarity:"👑 ELITE"},777:{points:777,rarity:"🍀 LUCKY"},666:{points:666,rarity:"😈 CURSED"},"000":{points:500,rarity:"⚡ RARE"},111:{points:400,rarity:"⚡ RARE"}},powerLevels:{0:{name:"Disabled",batchSize:0,interval:0},1:{name:"Minimal",batchSize:50,interval:200},2:{name:"Low",batchSize:100,interval:100},3:{name:"Light",batchSize:250,interval:50},4:{name:"Below Average",batchSize:500,interval:25},5:{name:"Standard",batchSize:1e3,interval:20},6:{name:"Above Average",batchSize:2e3,interval:15},7:{name:"High",batchSize:3e3,interval:10},8:{name:"Very High",batchSize:5e3,interval:8},9:{name:"Extreme",batchSize:1e4,interval:5},10:{name:"MAXIMUM POWER",batchSize:25e3,interval:1}}},this.workers={mouseover:null,manual:null,background:null},this.intervals={stats:null,performance:null},this.init()}init(){this.isInitialized||(this.isInitialized=!0,console.log("🧠 MINING BRAIN: Initializing centralized mining system..."),this.disableOldSystems(),this.createBrainUI(),this.setupMouseoverMining(),this.setupManualMining(),this.setupFormMining(),this.startPerformanceMonitoring(),this.loadState(),console.log("🧠 MINING BRAIN: Fully operational"))}disableOldSystems(){["mouseoverMiningV2","mouseoverMining","enhancedMiningDashboard","haichanMiner","emergencyMiner","haichanUnified"].forEach(s=>{if(window[s]){console.log(`🧠 MINING BRAIN: Disabling old system: ${s}`);try{typeof window[s].disable=="function"&&window[s].disable(),typeof window[s].stop=="function"&&window[s].stop(),typeof window[s].stopAllMining=="function"&&window[s].stopAllMining()}catch(r){console.log(`🧠 MINING BRAIN: Could not disable ${s}:`,r)}}}),["mining-dashboard","unified-mining-dashboard","enhanced-mining-dashboard","dashboard-toggle"].forEach(s=>{const r=document.getElementById(s);r&&(console.log(`🧠 MINING BRAIN: Removing old UI: ${s}`),r.remove())});for(let s=0;s<1e4;s++)clearInterval(s)}createBrainUI(){const e=`
            <div id="mining-brain-ui" class="mining-brain">
                <div class="brain-header" id="brain-header">
                    <div class="brain-title">
                        <span class="brain-icon">🧠</span>
                        <span class="brain-text">MINING BRAIN</span>
                        <span class="brain-status" id="brain-status">ACTIVE</span>
                    </div>
                    <div class="brain-controls">
                        <button id="brain-minimize" class="brain-btn">−</button>
                        <button id="brain-close" class="brain-btn">×</button>
                    </div>
                </div>

                <div class="brain-content" id="brain-content">
                    <!-- Power Control -->
                    <div class="brain-section">
                        <div class="section-title">⚡ Power Control</div>
                        <div class="power-control">
                            <div class="power-display">
                                <span id="power-level">${this.state.power}</span>/10
                                <span id="power-name">${this.configs.powerLevels[this.state.power].name}</span>
                            </div>
                            <input type="range" id="power-slider" class="power-slider"
                                   min="0" max="10" value="${this.state.power}" step="1">
                        </div>
                    </div>

                    <!-- Mode Selection -->
                    <div class="brain-section">
                        <div class="section-title">🎯 Mining Mode</div>
                        <div class="mode-selector">
                            <select id="mining-mode" class="mode-select">
                                <option value="mouseover" selected>Mouseover (Auto)</option>
                                <option value="manual">Manual Control</option>
                                <option value="background">Background Mining</option>
                                <option value="idle">Idle (Disabled)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Current Target -->
                    <div class="brain-section">
                        <div class="section-title">🔍 Current Target</div>
                        <div class="target-display" id="target-display">
                            Hover over content to begin mining
                        </div>
                    </div>

                    <!-- Live Statistics -->
                    <div class="brain-section">
                        <div class="section-title">📊 Live Statistics</div>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-label">Hash Rate</span>
                                <span class="stat-value" id="stat-hashrate">0 H/s</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Total Hashes</span>
                                <span class="stat-value" id="stat-hashes">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Proofs Found</span>
                                <span class="stat-value" id="stat-proofs">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Points Earned</span>
                                <span class="stat-value" id="stat-points">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Monitor -->
                    <div class="brain-section">
                        <div class="section-title">⚙️ Performance</div>
                        <div class="performance-display">
                            <div class="perf-item">
                                <span>CPU Load:</span>
                                <span id="cpu-load">0%</span>
                            </div>
                            <div class="perf-item">
                                <span>Avg Hash Time:</span>
                                <span id="hash-time">0ms</span>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Controls (when in manual mode) -->
                    <div class="brain-section" id="manual-controls" style="display: none;">
                        <div class="section-title">🎮 Manual Controls</div>
                        <div class="manual-buttons">
                            <button id="start-manual" class="brain-action-btn">Start Mining</button>
                            <button id="stop-manual" class="brain-action-btn">Stop Mining</button>
                        </div>
                        <div class="target-selector">
                            <select id="manual-target" class="target-select">
                                <option value="global">Global Mining</option>
                                <option value="custom">Custom Target</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Toggle Button -->
            <button id="brain-toggle" class="brain-toggle" title="Mining Brain">
                <div class="toggle-icon">🧠</div>
                <div class="toggle-text">BRAIN</div>
            </button>
        `;document.body.insertAdjacentHTML("beforeend",e),this.addBrainStyles(),this.setupBrainEvents()}addBrainStyles(){const e=document.createElement("style");e.id="mining-brain-styles",e.textContent=`
            .mining-brain {
                position: fixed;
                top: 20px;
                right: 20px;
                width: 320px;
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                border: 2px solid #00d4ff;
                border-radius: 12px;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                color: #ffffff;
                z-index: 999999;
                box-shadow: 0 8px 32px rgba(0, 212, 255, 0.3);
                backdrop-filter: blur(10px);
                transition: all 0.3s ease;
            }

            .brain-header {
                background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
                color: #1a1a2e;
                padding: 10px 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 10px 10px 0 0;
                cursor: move;
            }

            .brain-title {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: bold;
                font-size: 12px;
            }

            .brain-icon {
                font-size: 16px;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }

            .brain-status {
                background: rgba(0, 255, 136, 0.2);
                padding: 2px 6px;
                border-radius: 8px;
                font-size: 9px;
                color: #00ff88;
                border: 1px solid #00ff88;
            }

            .brain-controls {
                display: flex;
                gap: 5px;
            }

            .brain-btn {
                background: rgba(26, 26, 46, 0.3);
                border: none;
                color: #1a1a2e;
                padding: 4px 8px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                font-size: 10px;
                transition: all 0.2s ease;
            }

            .brain-btn:hover {
                background: rgba(26, 26, 46, 0.6);
                transform: scale(1.1);
            }

            .brain-content {
                padding: 15px;
                max-height: 600px;
                overflow-y: auto;
            }

            .brain-section {
                margin-bottom: 15px;
                padding: 10px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 8px;
                border: 1px solid rgba(0, 212, 255, 0.2);
            }

            .section-title {
                font-weight: bold;
                margin-bottom: 8px;
                color: #00d4ff;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .power-control {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .power-display {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            #power-level {
                font-size: 16px;
                font-weight: bold;
                color: #00ff88;
            }

            #power-name {
                color: #888;
                font-size: 9px;
            }

            .power-slider {
                width: 100%;
                height: 6px;
                border-radius: 3px;
                outline: none;
                appearance: none;
                background: linear-gradient(to right, #00d4ff 0%, #333 0%);
                cursor: pointer;
            }

            .power-slider::-webkit-slider-thumb {
                appearance: none;
                width: 16px;
                height: 16px;
                background: #00ff88;
                border-radius: 50%;
                cursor: pointer;
                box-shadow: 0 0 8px rgba(0, 255, 136, 0.5);
            }

            .mode-select, .target-select {
                width: 100%;
                background: #1a1a2e;
                color: #ffffff;
                border: 1px solid #00d4ff;
                border-radius: 6px;
                padding: 6px;
                font-size: 10px;
                font-family: inherit;
            }

            .target-display {
                padding: 8px;
                background: #1a1a2e;
                border: 1px solid rgba(0, 212, 255, 0.3);
                border-radius: 6px;
                color: #00d4ff;
                font-weight: bold;
                min-height: 20px;
                display: flex;
                align-items: center;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .stat-item {
                display: flex;
                justify-content: space-between;
                padding: 6px;
                background: #1a1a2e;
                border: 1px solid rgba(0, 212, 255, 0.2);
                border-radius: 4px;
            }

            .stat-label {
                color: #888;
                font-size: 9px;
            }

            .stat-value {
                color: #00ff88;
                font-weight: bold;
                font-size: 10px;
            }

            .performance-display {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .perf-item {
                display: flex;
                justify-content: space-between;
                font-size: 9px;
                color: #888;
            }

            .brain-action-btn {
                background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
                color: #1a1a2e;
                border: none;
                padding: 8px 16px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
                font-size: 10px;
                margin-right: 8px;
                transition: all 0.2s ease;
            }

            .brain-action-btn:hover {
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(0, 212, 255, 0.4);
            }

            .brain-toggle {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
                border: none;
                border-radius: 50%;
                color: #1a1a2e;
                cursor: pointer;
                font-family: 'Courier New', monospace;
                font-weight: bold;
                z-index: 999998;
                box-shadow: 0 4px 16px rgba(0, 212, 255, 0.4);
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 2px;
            }

            .brain-toggle:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0, 212, 255, 0.6);
            }

            .toggle-icon {
                font-size: 18px;
            }

            .toggle-text {
                font-size: 8px;
                font-weight: bold;
                letter-spacing: 1px;
            }

            /* Minimized state */
            .mining-brain.minimized .brain-content {
                display: none;
            }

            .mining-brain.minimized {
                width: auto;
            }

            /* Hidden state */
            .mining-brain.hidden {
                display: none;
            }

            /* Mining active indicators */
            .mining-active {
                box-shadow: 0 0 20px rgba(0, 255, 136, 0.8) !important;
                border: 2px solid #00ff88 !important;
                animation: miningGlow 1.5s infinite alternate;
            }

            @keyframes miningGlow {
                0% { box-shadow: 0 0 20px rgba(0, 255, 136, 0.8); }
                100% { box-shadow: 0 0 30px rgba(0, 255, 136, 1.0); }
            }
        `,document.head.appendChild(e)}setupBrainEvents(){var e,n;document.getElementById("brain-toggle").addEventListener("click",()=>{this.toggleBrain()}),document.getElementById("brain-minimize").addEventListener("click",()=>{this.minimizeBrain()}),document.getElementById("brain-close").addEventListener("click",()=>{this.hideBrain()}),document.getElementById("power-slider").addEventListener("input",s=>{this.setPower(parseInt(s.target.value))}),document.getElementById("mining-mode").addEventListener("change",s=>{this.setMode(s.target.value)}),(e=document.getElementById("start-manual"))==null||e.addEventListener("click",()=>{this.startManualMining()}),(n=document.getElementById("stop-manual"))==null||n.addEventListener("click",()=>{this.stopManualMining()}),this.setupDragging(),this.startUIUpdates()}setupDragging(){const e=document.getElementById("mining-brain-ui"),n=document.getElementById("brain-header");let s=!1,r={x:0,y:0};n.addEventListener("mousedown",o=>{if(o.target.classList.contains("brain-btn"))return;s=!0;const i=e.getBoundingClientRect();r={x:o.clientX-i.left,y:o.clientY-i.top},e.style.opacity="0.9"}),document.addEventListener("mousemove",o=>{if(!s)return;const i=o.clientX-r.x,a=o.clientY-r.y;e.style.left=Math.max(0,Math.min(i,window.innerWidth-e.offsetWidth))+"px",e.style.top=Math.max(0,Math.min(a,window.innerHeight-e.offsetHeight))+"px",e.style.right="auto"}),document.addEventListener("mouseup",()=>{s&&(s=!1,e.style.opacity="1")})}toggleBrain(){const e=document.getElementById("mining-brain-ui");e.style.display==="none"?(e.style.display="block",this.state.isMinimized=!1):e.style.display="none",this.saveState()}minimizeBrain(){const e=document.getElementById("mining-brain-ui"),n=document.getElementById("brain-minimize");e.classList.toggle("minimized"),this.state.isMinimized=e.classList.contains("minimized"),n.textContent=this.state.isMinimized?"+":"−",this.saveState()}hideBrain(){document.getElementById("mining-brain-ui").style.display="none",this.saveState()}setPower(e){this.state.power=Math.max(0,Math.min(10,e));const n=this.configs.powerLevels[this.state.power];document.getElementById("power-level").textContent=this.state.power,document.getElementById("power-name").textContent=n.name;const s=document.getElementById("power-slider"),r=this.state.power/10*100;s.style.background=`linear-gradient(to right, #00d4ff 0%, #00d4ff ${r}%, #333 ${r}%, #333 100%)`,console.log(`🧠 MINING BRAIN: Power set to ${this.state.power}/10 (${n.name})`),this.saveState(),this.state.mode!=="idle"&&this.state.currentTarget&&this.restartMining()}setMode(e){const n=this.state.mode;this.state.mode=e,console.log(`🧠 MINING BRAIN: Mode changed from ${n} to ${e}`),this.stopCurrentMode(),this.startCurrentMode(),document.getElementById("manual-controls").style.display=e==="manual"?"block":"none",this.saveState()}stopCurrentMode(){this.state.activeTargets.clear(),this.removeAllMiningVisuals(),Object.values(this.workers).forEach(e=>{e&&clearInterval(e)}),this.workers={mouseover:null,manual:null,background:null}}startCurrentMode(){switch(this.state.mode){case"mouseover":this.setupMouseoverMining();break;case"manual":break;case"background":this.startBackgroundMining();break}}setupMouseoverMining(){this.state.mode==="mouseover"&&(console.log("🧠 MINING BRAIN: Setting up mouseover mining"),document.removeEventListener("mouseover",this.handleMouseover),document.removeEventListener("mouseout",this.handleMouseout),this.handleMouseover=this.handleMouseover.bind(this),this.handleMouseout=this.handleMouseout.bind(this),document.addEventListener("mouseover",this.handleMouseover),document.addEventListener("mouseout",this.handleMouseout))}handleMouseover(e){if(this.state.mode!=="mouseover"||this.state.power===0)return;const n=this.getMineableTarget(e.target);n&&!this.state.activeTargets.has(n.id)&&this.startMining(n)}handleMouseout(e){const n=this.getMineableTarget(e.target);n&&this.state.activeTargets.has(n.id)&&setTimeout(()=>{e.target.matches(":hover")||this.stopMining(n)},100)}getMineableTarget(e){let n=e;for(let s=0;s<6&&n;s++){if(n.tagName==="IMG"&&n.src)return{id:n.src,type:"image",element:n,displayName:"🖼️ Image",points:25};if(n.classList&&n.classList.contains("post")){const r=n.querySelector(".post-no");if(r){const o=r.textContent.match(/No\.(\d+)/);if(o)return{id:`post-${o[1]}`,type:"post",element:n,displayName:`💬 Post #${o[1]}`,points:20}}}if(n.classList&&n.classList.contains("catalog-thread")){const r=n.dataset.threadId;if(r)return{id:`thread-${r}`,type:"thread",element:n,displayName:`🧵 Thread #${r}`,points:22}}n=n.parentElement}return null}async startMining(e){console.log(`🧠 MINING BRAIN: Starting mining on ${e.displayName}`),this.state.currentTarget=e,this.state.activeTargets.set(e.id,{target:e,startTime:Date.now(),hashes:0,worker:null}),this.addMiningVisual(e.element),this.updateTargetDisplay(e.displayName);const n=this.state.activeTargets.get(e.id);n.worker=setInterval(()=>{this.performMining(e)},this.configs.powerLevels[this.state.power].interval)}stopMining(e){console.log(`🧠 MINING BRAIN: Stopping mining on ${e.displayName}`);const n=this.state.activeTargets.get(e.id);n&&(clearInterval(n.worker),this.state.activeTargets.delete(e.id),this.removeMiningVisual(e.element)),this.state.currentTarget&&this.state.currentTarget.id===e.id&&(this.state.currentTarget=null,this.updateTargetDisplay("Hover over content to begin mining"))}async performMining(e){const n=this.configs.powerLevels[this.state.power].batchSize,s=performance.now();let r=0;const o=Math.min(n,1e3);for(let i=0;i<Math.ceil(n/o);i++){performance.now();const a=Math.min(o,n-i*o);for(let d=0;d<a;d++){const u=Math.floor(Math.random()*4294967295),l=`${e.type}-${e.id}-${Date.now()}-${u}`;try{const f=await this.sha256(l);r++,this.state.sessionStats.totalHashes++;const m=this.checkRarePattern(f);if(m){this.handleProofFound(f,u,l,m.pattern,m.points,e),this.updatePerformanceMetrics(s,r);return}for(const[w,p]of Object.entries(this.configs.patterns))if(f.startsWith(w)){this.handleProofFound(f,u,l,w,p.points,e),this.updatePerformanceMetrics(s,r);return}}catch(f){console.error("🧠 MINING BRAIN: Hash error:",f)}}i<Math.ceil(n/o)-1&&await new Promise(d=>setTimeout(d,0))}this.updatePerformanceMetrics(s,r)}updatePerformanceMetrics(e,n){const s=performance.now()-e;this.state.performance.avgHashTime=s/n;const r=n/(s/1e3);this.state.sessionStats.currentHashrate=Math.floor(r)}async sha256(e){if(window.wasmSha256)return window.wasmSha256(e);const n=new TextEncoder().encode(e),s=await crypto.subtle.digest("SHA-256",n);return Array.from(new Uint8Array(s)).map(o=>o.toString(16).padStart(2,"0")).join("")}checkRarePattern(e){for(const[n,s]of Object.entries(this.configs.rare))if(e.toLowerCase().includes(n.toLowerCase()))return{pattern:n,...s};return null}async handleProofFound(e,n,s,r,o,i){console.log(`🧠 MINING BRAIN: 💎 PROOF FOUND! ${r} (+${o} points)`),this.state.sessionStats.totalProofs++,this.state.sessionStats.totalPoints+=o,this.showProofCelebration(i.element,o);try{await this.submitProof({hash:e,nonce:n,data:s,pattern:r,points:o,target_type:i.type,target_id:i.id})}catch(a){console.error("🧠 MINING BRAIN: Submit error:",a)}}async submitProof(e){var s;return await(await fetch("/api/submit-proof",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":((s=document.querySelector('meta[name="csrf-token"]'))==null?void 0:s.content)||""},body:JSON.stringify(e)})).json()}addMiningVisual(e){e&&e.classList.add("mining-active")}removeMiningVisual(e){e&&e.classList.remove("mining-active")}removeAllMiningVisuals(){document.querySelectorAll(".mining-active").forEach(e=>{e.classList.remove("mining-active")})}showProofCelebration(e,n){if(!e)return;const s=document.createElement("div");s.textContent=`💎 +${n}!`,s.style.cssText=`
            position: fixed;
            color: #00ff88;
            font-weight: bold;
            font-size: 14px;
            z-index: 999999;
            pointer-events: none;
            animation: floatUp 2s ease-out forwards;
        `;const r=e.getBoundingClientRect();if(s.style.left=r.left+r.width/2+"px",s.style.top=r.top+r.height/2+"px",document.body.appendChild(s),setTimeout(()=>s.remove(),2e3),!document.getElementById("brain-celebration-styles")){const o=document.createElement("style");o.id="brain-celebration-styles",o.textContent=`
                @keyframes floatUp {
                    0% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                    100% { opacity: 0; transform: translate(-50%, -150px) scale(1.5); }
                }
            `,document.head.appendChild(o)}}updateTargetDisplay(e){document.getElementById("target-display").textContent=e}startUIUpdates(){this.intervals.stats=setInterval(()=>{this.updateStatsDisplay()},1e3),this.intervals.performance=setInterval(()=>{this.updatePerformanceDisplay()},5e3)}updateStatsDisplay(){const e=(Date.now()-this.state.sessionStats.startTime)/1e3,n=e>0?Math.floor(this.state.sessionStats.totalHashes/e):0;document.getElementById("stat-hashrate").textContent=`${n.toLocaleString()} H/s`,document.getElementById("stat-hashes").textContent=this.state.sessionStats.totalHashes.toLocaleString(),document.getElementById("stat-proofs").textContent=this.state.sessionStats.totalProofs.toString(),document.getElementById("stat-points").textContent=this.state.sessionStats.totalPoints.toFixed(1),this.state.sessionStats.currentHashrate=n}updatePerformanceDisplay(){const e=Math.min(100,this.state.sessionStats.currentHashrate/1e4*100);document.getElementById("cpu-load").textContent=`${e.toFixed(1)}%`,document.getElementById("hash-time").textContent=`${this.state.performance.avgHashTime.toFixed(2)}ms`}startManualMining(){if(this.state.mode!=="manual")return;const e={id:"manual-global",type:"manual",element:null,displayName:"🎮 Manual Mining",points:100};this.startMining(e)}stopManualMining(){this.state.currentTarget&&this.state.currentTarget.type==="manual"&&this.stopMining(this.state.currentTarget)}startBackgroundMining(){console.log("🧠 MINING BRAIN: Starting background mining");const e={id:"background-global",type:"background",element:null,displayName:"🌐 Background Mining",points:50};this.startMining(e)}restartMining(){if(this.state.currentTarget){const e=this.state.currentTarget;this.stopMining(e),setTimeout(()=>this.startMining(e),100)}}saveState(){const e={power:this.state.power,mode:this.state.mode,isMinimized:this.state.isMinimized};localStorage.setItem("mining-brain-state",JSON.stringify(e))}loadState(){try{const e=localStorage.getItem("mining-brain-state");if(e){const n=JSON.parse(e);this.setPower(n.power||5),this.setMode(n.mode||"mouseover"),n.isMinimized&&this.minimizeBrain()}}catch(e){console.error("🧠 MINING BRAIN: Error loading state:",e)}}startPerformanceMonitoring(){setInterval(()=>{performance.memory&&(this.state.performance.memoryUsage=performance.memory.usedJSHeapSize/1024/1024)},1e4)}destroy(){var e,n,s,r;console.log("🧠 MINING BRAIN: Shutting down"),this.stopCurrentMode(),Object.values(this.intervals).forEach(o=>{o&&clearInterval(o)}),document.removeEventListener("mouseover",this.handleMouseover),document.removeEventListener("mouseout",this.handleMouseout),(e=document.getElementById("mining-brain-ui"))==null||e.remove(),(n=document.getElementById("brain-toggle"))==null||n.remove(),(s=document.getElementById("mining-brain-styles"))==null||s.remove(),(r=document.getElementById("brain-celebration-styles"))==null||r.remove(),this.isInitialized=!1}}console.log("🧠 MINING BRAIN: Loading...");window.haichanMiningBrain&&window.haichanMiningBrain.destroy();window.haichanMiningBrain=new nr;window.haichanMiner=window.haichanMiningBrain;console.log("🧠 MINING BRAIN: System operational");document.addEventListener("DOMContentLoaded",()=>{document.documentElement.setAttribute("data-theme","classic"),document.body.className=document.body.className.replace(/theme-\w+/g,""),document.body.classList.add("theme-classic"),localStorage.getItem("haichan-theme")&&localStorage.removeItem("haichan-theme"),console.log("🔒 Theme permanently locked to classic")});
