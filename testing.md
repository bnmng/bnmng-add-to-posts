# Testing #
## Test 1: Add text with default settings #

Create a post (post of type post)  with the following parameters:
* It is of the category, 'Uncategorized'
* Give it the title 'BEOWULF 1-19'
* Add to the content the text in the appendix section of this document under 'BEOWULF 1-19'

Create an instance with the following parameters
* Post Types = post
* Singular view is checked
* No categories are selected
* No author is selected
* Add to Beginning of Post = "** This site is for demonstrating the plugin [bnmng Add to Posts]  **"  
* Add to End of Post = ""**  This is only a demonstration **"

View the the post loop.  Ensure the added text does not appear on the loop

View the post individually.  Ensure the following; 

* "** This site is for demonstrating the plugin [bnmng Add to Posts]  **"  appears at the top of the content
* ""**  This is only a demonstration **"  appears at the bottom of the content
  
## Test 2: Add text with a certain category selected #

Ensure the post from Step 1 exists

Create a new user and name him "Albert"

Use the email address "Albert@bnmng.com"

Give him/her Author privileges

Give the password "Aelibnes#001"

Log in as Albert and create a post with the following parameters:

Create a post (post of type post) with the following parameters:
* It is of the category, 'Relativity'
* It contains some discernable text

Create an instance with the following parameters
* Post Types = post
* Singular view is checked
* No category Einstien is selected
* No author is selected
* Add to Beginning of Post = "** Beginning of Type Post, Singular, Einstein, Any Author,  **",  
* Add to End of Post = ""** End of Type Post, Singular, Einstein, Any Author,  **",

View the the post loop.  Ensure the added text does not appear on the loop

View the uncategorized post individually.  Ensure the following; 

*  "** This site is for demonstrating the plugin [bnmng Add to Posts]  **"   appears at the top of the content
* "**  This is only a demonstration **"  appears at the bottom of the content

View the Einstein categorized post individually.  Ensure the following; 

* "** This site is for demonstrating the plugin [bnmng Add to Posts]  **"    appears at the top of the content
* "** Beginning of Type Post, Singular, Einstein Selected, Any Author,  **"  appears after the above text

* "** End of Type Post, Singular, Einstein Selected, Any Author,  **"  appears at the bottom of the content
*  ""**  This is only a demonstration **" appears after the above text

## Test 3: Att text to posts of a certain author

Create a new user and name him/her "Nicola"

Use the email address "nicola@bnmng.com"

Give him/her Author privileges

Give the password "Ntiecsol#001"

Log in as Nicola and create a post

# Appendix #

## Text to be Added ##

### BEOWULF 1-19 ###

Lo! the Spear-Dane's glory through splendid achievements
The folk-kings' former fame we have heard of,
How princes displayed then their prowess-in-battle.
Oft Scyld the Scefing from scathers in numbers
From many a people their mead-benches tore.
Since first he found him friendless and wretched,
The earl had had terror: comfort he got for it,
Waxed 'neath the welkin, world-honor gained,
Till all his neighbors o'er sea were compelled to
Bow to his bidding and bring him their tribute:
An excellent atheling! After was borne him
A son and heir, young in his dwelling,
Whom God-Father sent to solace the people.
He had marked the misery malice had caused them,
1That reaved of their rulers they wretched had erstwhile2
Long been afflicted. The Lord, in requital,
Wielder of Glory, with world-honor blessed him.
Famed was Beowulf, far spread the glory
Of Scyld's great son in the lands of the Danemen.

### Special Theory of Relativity Part1 Ch1 Paras 1-2 ##

In your schooldays most of you who read this book made acquaintance with the noble building of Euclid's geometry, and you remember - perhaps with more respect than love - the magnificent structure, on the lofty staircase of which you were chased about for uncounted hours by conscientious teachers. By reason of our past experience, you would certainly regard everyone with disdain who should pronounce even the most out-of-the-way proposition of this science to be untrue. But perhaps this feeling of proud certainty would leave you immediately if some one were to ask you: "What, then, do you mean by the assertion that these propositions are true?" Let us proceed to give this question a little consideration.

Geometry sets out form certain conceptions such as "plane," "point," and "straight line," with which we are able to associate more or less definite ideas, and from certain simple propositions (axioms) which, in virtue of these ideas, we are inclined to accept as "true." Then, on the basis of a logical process, the justification of which we feel ourselves compelled to admit, all remaining propositions are shown to follow from those axioms, i.e. they are proven. A proposition is then correct ("true") when it has been derived in the recognised manner from the axioms. The question of "truth" of the individual geometrical propositions is thus reduced to one of the "truth" of the axioms. Now it has long been known that the last question is not only unanswerable by the methods of geometry, but that it is in itself entirely without meaning. We cannot ask whether it is true that only one straight line goes through two points. We can only say that Euclidean geometry deals with things called "straight lines," to each of which is ascribed the property of being uniquely determined by two points situated on it. The concept "true" does not tally with the assertions of pure geometry, because by the word "true" we are eventually in the habit of designating always the correspondence with a "real" object; geometry, however, is not concerned with the relation of the ideas involved in it to objects of experience, but only with the logical connection of these ideas among themselves.
