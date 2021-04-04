import React from 'react';
import Helmet from 'react-helmet';
import { usePage } from '@inertiajs/inertia-react'
import { Container } from 'reactstrap';

export default function Layout({ title, children }) {
    const { app } = usePage().props
  return (
    <div>
      <Helmet titleTemplate={`%s | ${app.name}`} title={title} />
        <main class="py-4">
            <Container>
            {/* Navbar */}
            {/* Breadcrumbs */}
                {children}
            </Container>
        </main>
    </div>
  );
}
