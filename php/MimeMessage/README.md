MIME Message Parser
===================

This class provides a well-abstracted MIME format message parser.
I found several MIME parser classes with terrible interfaces.  This
provides a powerful and more simplified interface that actually
uses OOP methodology (not just a pile of associative arrays).

I've read through [RFC2822](http://www.faqs.org/rfcs/rfc2822.html)
and this class should decode a modern, compliant message.  With
the possible variations in message generators, I can't be sure this
is a one-size-fits-all solution, and it, honestly, isn't my intention
to make something that universal.  I will continue to tweak and adjust
the implementation as I see more message formats that break the current
(reasonably robust) parsing methods.

Known Non-conformance with RFC2822
----------------------------------

### Header Data Fields ###

- Nested '(' or ')' inside comments will break comment stripping.
- The parser allows nonstandard line endings (for compatibility).
- Obsolete forms may impact parsing (untested).
