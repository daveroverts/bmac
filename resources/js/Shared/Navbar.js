import { InertiaLink, usePage } from "@inertiajs/inertia-react";
import React, { useState } from "react";
import { format } from "date-fns";
import {
    Collapse,
    Navbar as BaseNavbar,
    NavbarToggler,
    Nav,
    NavItem,
    NavLink,
    UncontrolledDropdown,
    DropdownToggle,
    DropdownMenu,
    DropdownItem,
    Container,
} from "reactstrap";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronRight } from "@fortawesome/free-solid-svg-icons";

const Navbar = () => {
    const [isOpen, setIsOpen] = useState(false);

    const { app, auth, navbar } = usePage().props;
    const toggle = () => setIsOpen(!isOpen);

    return (
        <BaseNavbar dark expand="md" className="navbar-laravel">
            <Container>
                <InertiaLink href="/" className="navbar-brand">
                    <img src="images/division-square.png" height={45}></img>
                </InertiaLink>

                <InertiaLink href="/" className="navbar-brand">
                    {app.name}
                </InertiaLink>

                <NavbarToggler onClick={toggle} />
                <Collapse isOpen={isOpen} navbar>
                    <Nav className="mr-auto" navbar>
                        <UncontrolledDropdown nav inNavbar>
                            <DropdownToggle nav caret>
                                Events
                            </DropdownToggle>
                            <DropdownMenu>
                                <InertiaLink
                                    href={route("home")}
                                    className={`dropdown-item ${route().current('home') ? 'active' : null}`}
                                >
                                    Overview
                                </InertiaLink>
                                {navbar.events.map(
                                    ({ name, slug, startEvent, bookings }) => (
                                        <div key={slug}>
                                            <DropdownItem divider />
                                            <InertiaLink
                                                href={route(
                                                    "bookings.event.index",
                                                    slug
                                                )}
                                                className="dropdown-item"
                                            >
                                                {name} -{" "}
                                                {format(
                                                    new Date(startEvent),
                                                    "PP"
                                                )}
                                            </InertiaLink>
                                            {bookings != undefined
                                                ? bookings.map(
                                                      ({ uuid, callsign }) => (
                                                          <div key={uuid}>
                                                              <InertiaLink
                                                                  href={route(
                                                                      "bookings.show",
                                                                      uuid
                                                                  )}
                                                                  className="dropdown-item"
                                                              >
                                                                  <FontAwesomeIcon
                                                                      icon={
                                                                          faChevronRight
                                                                      }
                                                                  />{" "}
                                                                  {bookings.length >
                                                                  1 ? (
                                                                      <>
                                                                          {
                                                                              callsign
                                                                          }
                                                                      </>
                                                                  ) : (
                                                                      "My booking"
                                                                  )}
                                                              </InertiaLink>
                                                          </div>
                                                      )
                                                  )
                                                : null}
                                        </div>
                                    )
                                )}
                            </DropdownMenu>
                        </UncontrolledDropdown>
                        <NavItem>
                            <InertiaLink
                                href={route("faq")}
                                className={`nav-link ${route().current('faq') ? 'active' : null}`}
                            >
                                FAQ
                            </InertiaLink>
                        </NavItem>
                        <NavItem>
                            <NavLink href={"mailto:" + app.contact_mail}>
                                Contact Us
                            </NavLink>
                        </NavItem>
                        {auth.user && auth.user.is_admin ? (
                            <UncontrolledDropdown nav inNavbar active={route().current('admin.*')}>
                                <DropdownToggle nav caret>
                                    Admin
                                </DropdownToggle>
                                <DropdownMenu>
                                    <InertiaLink
                                        href={route("admin.events.index")}
                                        className={`dropdown-item ${route().current('admin.events.*') ? 'active' : null}`}
                                    >
                                        Events
                                    </InertiaLink>
                                    <InertiaLink
                                        href={route("admin.airports.index")}
                                        className={`dropdown-item ${route().current('admin.airports.*') ? 'active' : null}`}
                                    >
                                        Airports
                                    </InertiaLink>
                                    <InertiaLink
                                        href={route("admin.faq.index")}
                                        className={`dropdown-item ${route().current('admin.faq.*') ? 'active' : null}`}
                                    >
                                        FAQ
                                    </InertiaLink>
                                </DropdownMenu>
                            </UncontrolledDropdown>
                        ) : null}
                    </Nav>
                    <Nav className="ml-auto" navbar>
                        {auth.user ? (
                            <UncontrolledDropdown nav inNavbar>
                                <DropdownToggle nav caret>
                                    {auth.user.name}
                                </DropdownToggle>
                                <DropdownMenu>
                                    <InertiaLink
                                        href={route("user.settings.edit")}
                                        className={`dropdown-item ${route().current('user.settings.edit') ? 'active' : null}`}
                                    >
                                        My settings
                                    </InertiaLink>
                                    <InertiaLink
                                        href={route("logout")}
                                        className="dropdown-item"
                                    >
                                        Logout
                                    </InertiaLink>
                                </DropdownMenu>
                            </UncontrolledDropdown>
                        ) : (
                            <NavItem>
                                <NavLink href="/login">Login</NavLink>
                            </NavItem>
                        )}
                    </Nav>
                </Collapse>
            </Container>
        </BaseNavbar>
    );
};

export default Navbar;
