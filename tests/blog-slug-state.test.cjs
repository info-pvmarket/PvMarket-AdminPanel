const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const vm=require('node:vm');
const view=fs.readFileSync(path.join(__dirname,'../resources/views/admin/knowledge-hub/blogs/blogs.blade.php'),'utf8');
const source=view.slice(view.indexOf('    function toSlug(str)'),view.indexOf('})();',view.indexOf('    function toSlug(str)')));
function field(value=''){return {value,dataset:{},listeners:{},addEventListener(event,handler){this.listeners[event]=handler;},input(value){this.value=value;this.listeners.input.call(this);}};}
function editor(slug){const headingInput=field('Original title'),slugInput=field(slug),slugPreview={textContent:''};vm.runInNewContext(source,{headingInput,slugInput,slugPreview});return {headingInput,slugInput};}
test('title input preserves an existing published slug',()=>{const {headingInput,slugInput}=editor('how-do-you-register-as-a-seller');headingInput.input('A revised seller guide');assert.equal(slugInput.value,'how-do-you-register-as-a-seller');});
test('new unsaved blank slug follows title until explicitly edited',()=>{const {headingInput,slugInput}=editor('');headingInput.input('A New Guide');assert.equal(slugInput.value,'a-new-guide');slugInput.input('chosen-address');headingInput.input('Another title');assert.equal(slugInput.value,'chosen-address');});
test('clearing the slug does not silently regenerate it on a later title edit',()=>{const {headingInput,slugInput}=editor('existing');slugInput.input('');headingInput.input('Changed title');assert.equal(slugInput.value,'');});
