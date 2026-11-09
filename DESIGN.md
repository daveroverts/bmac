---
name: Book me a Cookie
description: Precise, scannable VATSIM event booking, built on Bootstrap and re-themed per division.
colors:
  primary: "#2C3E50"
  secondary: "#95a5a6"
  tertiary: "#18BC9C"
  success: "#18BC9C"
  info: "#3498db"
  warning: "#F39C12"
  danger: "#E74C3C"
  surface: "#ffffff"
  surface-muted: "#ecf0f1"
  text: "#212529"
  status-unavailable: "#7b8a8b"
typography:
  page-title:
    fontFamily: "Lato, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif"
    fontSize: "2rem"
    fontWeight: 400
    lineHeight: 1.2
  event-title:
    fontFamily: "Lato, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 700
    lineHeight: 1.2
  body:
    fontFamily: "Lato, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
  table-header:
    fontFamily: "Lato, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif"
    fontSize: "1rem"
    fontWeight: 700
    lineHeight: 1.5
rounded:
  control: "6px"
  breadcrumb: "4px"
spacing:
  table-cell: "8px"
  control-y: "6px"
  control-x: "12px"
  page-y: "24px"
components:
  button-book:
    backgroundColor: "{colors.success}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
    padding: "6px 12px"
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
    padding: "6px 12px"
  button-mine:
    backgroundColor: "{colors.info}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
    padding: "6px 12px"
  button-unavailable:
    backgroundColor: "{colors.status-unavailable}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
    padding: "6px 12px"
  button-danger:
    backgroundColor: "{colors.danger}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
    padding: "6px 12px"
  breadcrumb:
    backgroundColor: "{colors.surface-muted}"
    rounded: "{rounded.breadcrumb}"
    padding: "12px 16px"
  navbar:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
  action-bar-empty:
    backgroundColor: "{colors.surface-muted}"
    textColor: "{colors.text}"
    rounded: "{rounded.control}"
  action-bar-selected:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
---

# Design System: Book me a Cookie

## Overview

**Creative North Star: "The Flight Board"**

BMAC reads like a departures board: slots are rows to scan, status is readable at a glance, and each row leads to one clear action. The mood is precise and operational. It uses aviation's own terms, Zulu times and exact counts, and it says what an action will affect before the action runs.

Components are compact and scannable, built on stock Bootstrap with the Bootswatch Flatly theme. Short labels and tight rows do the work, not custom controls. Pages are a navy navbar, a white body and a single centred container, with no decoration beyond the division's logo and event images.

This is the default identity. Each division can swap its name, logos and six theme colours at build time, so the values below are defaults and not fixed brand colours.

**Key Characteristics:**
- Stock Bootstrap 5 components with the Flatly theme.
- Flat white pages, with tables as the main working surface.
- Status and consequences stated in exact words and counts.
- Colours come from theme variables, so any division can replace them.

## Colors

A muted navy and white base, with saturated flat colours reserved for actions and status.

### Primary
- **Flight Deck Navy:** Navbar background, general action buttons, event titles, selected filters and the action bar when bookings are selected.

### Secondary
- **Overcast Grey:** Secondary buttons, plus body and breadcrumb links.

### Tertiary
- **Runway Teal:** Navbar link hover and active state. By default it is the same value as success, but divisions set the two separately.

### Semantic
- **Go Teal (success):** The action a pilot wants next, such as "BOOK NOW", form confirmation buttons and the Open status.
- **Sky Blue (info):** The pilot's own items, such as "My booking" and "My Reservation", and the login prompt in a slot row.
- **Amber (warning):** The Reserved status in admin tables.
- **Signal Red (danger):** Destructive actions, errors and "Not available". Also used for the "You already have a booking" message.

### Neutral
- **White:** Page, table and navbar text.
- **Cloud:** Breadcrumb background and the action bar when nothing is selected.
- **Ink:** Body text and headings.
- **Slate Grey:** Taken slots on the pilot table, shown as disabled "Booked" and "Reserved" buttons.

### Named Rules
**The Swappable Colour Rule.** Use Bootstrap theme classes and Sass variables, never literal hex values, because divisions set primary, secondary, tertiary, success, warning and danger through `BOOTSTRAP_COLOR_*` at build time.

**The Status Colour Rule.** Teal means available. On pilot tables, blue means yours, slate means taken and red means not available. On admin tables, Open is teal, Reserved is amber and Booked is neutral. Status always carries a text label as well as colour.

## Typography

**Body Font:** Lato (with the system sans-serif stack as fallback)
**Label/Mono Font:** Bootstrap's monospace stack, for callsign and aircraft cells when the user turns on the monospace setting

**Character:** One humanist sans at two self-hosted weights, regular and bold, which keeps the interface neutral and leaves emphasis to size and weight.

### Hierarchy
- **Page title** (regular, 2rem, 1.2): The main heading on a page, rendered as `h3` and followed by a divider. Its declared weight of 500 resolves to the loaded regular face.
- **Event title** (bold, 1.5rem, 1.2): Event names in the home listing, as navy underlined links.
- **Table header** (bold, 1rem, 1.5): Column labels in slot and admin tables.
- **Body** (regular, 1rem, 1.5): Paragraphs, table cells, buttons and form text.

### Named Rules
**The Two Weights Rule.** Only Lato 400 and 700 are loaded, so use those two weights. Other values resolve to one of them, including the navbar brand's 800, which renders at 700.

**The Zulu Time Rule.** Show times in 24-hour format with a `z` suffix, as in `18:00z` or `1800z`.

## Layout

Every page uses a single Bootstrap `.container` with 24px vertical padding on `main`, under the navbar and above the footer. The order is always navbar, breadcrumb, page title, divider, then content.

Layout follows Bootstrap's 12-column grid and default breakpoints. The navbar collapses to a toggler below `md`. Home event entries split into two equal columns from `lg`, and stack below that. Spacing uses Bootstrap's utility scale, with 8px table cell padding. Wide tables scroll inside `.table-responsive`, and the page itself never scrolls sideways.

## Elevation & Depth

The system is flat. Hierarchy comes from dividers, borders and tinted surfaces. The one authored shadow is a faint line under the navbar. Dropdowns and modals keep their Bootstrap defaults.

### Shadow Vocabulary
- **Navbar edge** (`box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04)`): Separates the navbar from the page.

### Named Rules
**The Flat Page Rule.** Content surfaces stay flat at rest. Only floating layers such as dropdowns and modals get a shadow.

## Shapes

Corners are gently rounded (6px) on buttons, inputs and similar controls, slightly tighter (4px) on the breadcrumb, with square tables and a full-width navbar. Event images use Bootstrap's `rounded` class. No custom silhouettes or clipping.

## Components

### Buttons
Short, solid and obvious. Use `btn-sm` in admin tables and the action bar.
- **Shape:** Gently curved (6px), with 6px by 12px padding at body size.
- **Primary:** Navy for general actions, such as admin tools and page actions.
- **Book:** Teal for the action a pilot wants next, and to confirm forms.
- **Mine:** Blue for links to the pilot's own booking or reservation.
- **Unavailable:** Slate and disabled, showing "Booked" or "Reserved".
- **Danger:** Red for destructive actions and "Not available".
- **Hover / Focus:** Bootstrap defaults. New form buttons use `x-forms.button` with a `variant`.

### Chips
- **Status badges:** On admin tables, subtle tinted badges with a border and dark text: Open, Reserved and Booked.

### Cards / Containers
Plain Bootstrap cards with a header and body wrap forms and detail pages. They add no shadow.

### Inputs / Fields
- **Style:** Bootstrap form controls built with the `x-forms.*` components where possible, which handle label, help text and validation together. Read-only values use plaintext form controls.
- **Enhanced inputs:** Flatpickr for date and time, Tom Select for searchable dropdowns, and TinyMCE for rich text.
- **Error:** Bootstrap's `is-invalid` state with invalid feedback under the field.

### Navigation
- **Navbar:** Full-width navy bar with the division square logo, site title, dropdown menus and a right-aligned account menu. Links are white, turning teal on hover and when active.
- **Breadcrumb:** Cloud strip at the top of every page.
- **Pagination:** Bootstrap pagination in Flatly's teal, under long admin tables.

### Dialogs
- **Modals:** Bootstrap modals hold bulk edit, email and delete. They use no fade, a static backdrop, a scrollable body with a fixed footer, and go fullscreen on phones. Livewire state opens and closes them.
- **Alerts:** SweetAlert2 shows flash messages and simple confirmations, through the `layouts.alert` partial and the `confirm-button` component.

### Slot Table
The signature component and the Flight Board itself. It is a hoverable Bootstrap table with From, To, optional CTOT and ETA, Callsign, Aircraft and a final action column. Airport names are dotted-underline abbreviations. The signed-in pilot's own row uses the active table highlight. A "Flights available" count sits above the table, and it refreshes through Livewire polling when configured.

### Action Bar
The admin counterpart to the slot table's one action per row. It sits between filters and table, stays visible with nothing selected, and is sticky from `md` up. Cloud when empty with disabled actions, navy with a selection. It states the count and who is affected, and selected rows use the active table highlight.

## Do's and Don'ts

### Do:
- **Do** reach for Bootstrap components and existing `x-forms.*` components before writing custom ones.
- **Do** use theme classes such as `btn-success` and `text-primary` so division colours apply.
- **Do** state exact counts and who is affected before a bulk or destructive action runs.
- **Do** keep status readable as words, not colour alone.
- **Do** test new interface with different division colours, logos and rich-text event content.

### Don't:
- **Don't** hard-code colours, logos or a division name.
- **Don't** add shadows to content surfaces at rest.
- **Don't** load extra Lato weights or a second typeface without changing this document.
- **Don't** add fade to Livewire-driven Bootstrap modals, because Bootstrap ignores show and hide during a transition.
- **Don't** treat the current default contrast as a target for new text. White on Go Teal (about 2.4:1) and Overcast Grey links on white (about 2.6:1) fall below WCAG AA.
