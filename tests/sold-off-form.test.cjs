const {test}=require('node:test');const assert=require('node:assert/strict');const fs=require('fs');const vm=require('vm');
const blade=fs.readFileSync('resources/views/admin/product_listing/edit.blade.php','utf8');
test('sold-off masks tier values without changing configured hidden data and restores lead time',()=>{
 const nodes={toggleSoldOff:{checked:true},totalQtyInput:{value:'10'},leadTimeInput:{value:'3',dataset:{configuredLeadTime:'3'},readOnly:false},totalQtyUnit:{textContent:'pallets'},slotList:{innerHTML:'',rows:[],appendChild(row){this.rows.push(row)}},slotInputs:{innerHTML:''},summTiers:{},tierFraction:{}};
 const buttons=[{}];const labels=[{}];
 const slots=[{min_quantity:1,max_quantity:3,price:100,commission_percentage:3,total_price:103}];
 const context={slots,document:{getElementById:id=>nodes[id],querySelectorAll:q=>q==='.btn-add-slot'?buttons:labels,createElement:()=>({})},syncQuantityPresentation:()=>{},syncInventoryNotesVisibility:()=>{},cancelSlot:()=>{},hasSpecificMaxQuantity:s=>Number(s.max_quantity)>0};
 vm.createContext(context);
 for(const name of ['renderSlots','syncSoldOffQuantity'])vm.runInContext(blade.match(new RegExp('function '+name+'\\(\\) \\{[\\s\\S]*?\\n\\}'))[0],context);
 context.syncSoldOffQuantity();
 assert.equal(nodes.totalQtyInput.value,'0');assert.equal(nodes.leadTimeInput.value,'0');assert.equal(nodes.leadTimeInput.readOnly,true);assert.equal(buttons[0].disabled,true);
 const row=nodes.slotList.rows.at(-1).innerHTML;assert.match(row,/0 pallets – 0 pallets/);assert.match(row,/0\.00/);assert.ok(!row.includes('103.00'));assert.match(row,/\+ 0%/);
 assert.match(nodes.slotInputs.innerHTML,/\[price\]"\s+value="100"/);assert.equal(slots[0].min_quantity,1);
 nodes.toggleSoldOff.checked=false;context.syncSoldOffQuantity();
 assert.equal(nodes.totalQtyInput.value,'0');assert.equal(nodes.leadTimeInput.value,'3');assert.equal(nodes.leadTimeInput.readOnly,false);assert.equal(buttons[0].disabled,false);assert.match(nodes.slotList.rows.at(-1).innerHTML,/103\.00/);
});
