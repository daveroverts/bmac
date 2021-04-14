import Layout from "../../Shared/Layout";
import { usePage, useForm } from "@inertiajs/inertia-react";
import { Button, CustomInput, Form, FormGroup, Label } from 'reactstrap';


const Edit = () => {
    const { user } = usePage().props;
    const { data, setData, put, processing, errors } = useForm({
        airport_view: user.airport_view || 0,
        use_monospace_font: user.use_monospace_font || 0,
      })

      function submit(e) {
        e.preventDefault()

        put(route('user.settings.update'));
      }


      return (
        <Form onSubmit={submit}>
        <FormGroup>
          <Label for="airport_view">Default Airport View</Label>
          <div>
            <CustomInput type="radio" id="airport_view0" name="airport_view" label="Name" inline value={data.airport_view} onChange={e => setData('airport_view', 0)}/>
            <CustomInput type="radio" id="airport_view1" name="airport_view" label="ICAO" inline value={data.airport_view} onChange={e => setData('airport_view', 1)}/>
            <CustomInput type="radio" id="airport_view2" name="airport_view" label="IATA" inline value={data.airport_view} onChange={e => setData('airport_view', 2)} />
          </div>
        </FormGroup>
        <FormGroup>
          <Label for="airport_view">Use monspace font</Label>
          <div>
            <CustomInput type="radio" id="use_monospace_font0" name="use_monospace_font" label="No" inline value={data.use_monospace_font} onChange={e => setData('use_monospace_font', 0)}/>
            <CustomInput type="radio" id="use_monospace_font1" name="use_monospace_font" label="Yes" inline value={data.use_monospace_font} onChange={e => setData('use_monospace_font', 1)}/>
          </div>
        </FormGroup>

        <Button color="primary" disabled={processing}>Submit</Button>
      </Form>
      );
}

Edit.layout = (page) => <Layout children={page} title="My settings" />;

export default Edit;
