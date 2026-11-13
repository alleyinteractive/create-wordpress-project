# Comments

Applies to all file types. Comment the why: the constraint, trade-off, or
browser quirk that a later reader would otherwise "clean up", for example "Do
not float images on small screens." The code carries the what. Rationale for a
design choice belongs in the PR description. Keep comments and UI copy plain
and specific.

In PHP that means a docblock that states the contract (parameters, return, when
it fires) and an inline comment only for a non-obvious constraint.
