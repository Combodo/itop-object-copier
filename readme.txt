/////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Object copy
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

This modules allows to prefill new objects with existing object data.
From a source object, users have a new menu. Clicking on this menu opens the object creation form with some values already set.

Several usages are possible:
 * cloning: copy all the data (excepted some fields)
 * create child/parent ticket from an existing ticket
 * mutate an object, for instance turn a server into a virtual server

Configuration
See module.itop-object-copier.php for examples.

For any source object, you will have to define a rule:
 * source_scope: an OQL (no parameter)
 * allowed_profiles: A CSV list of profiles. If no profile is defined then anybody can see the menu
 * label: Label or dictionary entry
 * dest_class: Class of the object to create
 * preset: Series of actions to preset the object in the creation form
 * retrofit: Series of actions to retrofit some information from the created object to the source object

The actions availables to preset or retrofit are the same.
Anyhow, the read/written objects will vary depending on the situation:
 * preset: reads the source object and writes the new object
 * retrofit: reads the new object and writes the source object


List of actions:
 * clone_scalars(): clone all scalar attributes
 * clone(att1,att2,...): clone the given attributes
 * reset(att1): reset the attribute to its default value
 * copy(attRead,attWrite): copy an attribute ('id' can be used here)
 * append(att,string): appends a literal to the attribute
 * set(att,value): sets a value
 * add_to_list(attRead,attWrite,attLink,value): attRead is an external key on the read object, attWrite is a N-N link set on the written object, attLink is an attribute on the link class that will be set to <value>. 
        