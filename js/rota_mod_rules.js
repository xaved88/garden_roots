// FOR THE EXECUTING OF AUTOMATION RULES AND FUNCTIONS:

var rules = [
	{
		type:'standard',
		position:7,
		shift:10,
		staff: false,
		amount: 1,
		type: 2, // 2 staff, 1 volunteers
		priority: {
			rq3: 3,
			equality: 2,
			partner: 1
			// contract: 0,
			// needs: 0
			// no_double_pos
			// no_double_shift
		}
	}
];
var staff = [];

run_rule = function(rule){
	console.log(rule);
	var table = $("#calendar tbody");
	staff = [];
	var staff_ids = [];
	var staff_cells = table.find('th'); // all the staff that can do the position and are of the right type
	staff_cells.each(function(){
		var t = $(this);
		var push = true;
		if(rule.type && t.attr('data-staff_type') != rule.type)
			push = false;
		
		if(push){
			var pos = t.find("div.staff_position[data-pos_id="+rule.position+"]");
			if(!pos.length){
				push = false;
			}
			else{
				s = {}
				s.staff_id = t.attr('data-staff');
				s.partner = 0; // add later
				s.pos_pref = pos.attr('data-pref');
				s.pos_min = pos.attr('data-min');
				s.pos_max = pos.attr('data-max');
				staff_ids.push(s.staff_id);
				staff.push(s);
			}
		}
	});
	
	var dstafftext = "[data-staff=";
	for(i=0; i<staff.length; i++)
		dstafftext += staff[i].staff_id + "],[data-staff=";
	dstafftext += "]";
	var cells = $("#calendar tbody td[data-shift="+rule.shift+"]"+dstafftext); // all the cells relating to the shift
	/*
	for(i = 0; i < cells.length; i++){
		sid = $(cells[i]).attr('data-staff');
		if(staff_ids.indexOf(sid) == -1){
			delete cells[i];
		}
	}
	*/
	console.log(cells);
	console.log(staff);
}
	
run_rules = function(){
	
	for(i=0; i < rules.length; i++){
		run_rule(rules[i]);
	}
}