# Comments

Applies to all file types. Comments say why, never what. The code
already says what it does; comment the constraint, trade-off, or browser quirk
that a later reader would otherwise "clean up", for example "Do not float images
on small screens." Do not restate a name or a type, and do not narrate a change.
Rationale for a design choice belongs in the PR description. Keep comments and
UI copy plain and specific; cut anything that reads as generated filler.

In PHP that means a docblock that states the contract (parameters, return, when
it fires) and an inline comment only for a non-obvious constraint.
