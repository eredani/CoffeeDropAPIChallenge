import React, { Component } from 'react';
import { Button,Container,Row,Col, Form, FormGroup, Label, Input } from 'reactstrap';
import ReactDOM from 'react-dom';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import axios from 'axios';
export default class App extends Component {
    constructor(props)
    {
        super(props);
        this.state = ({
            Ristretto:0,
            Espresso:0,
            Lungo:0,
            Postcode:'',
            nearLocationTime:null,
            nearLocationAdd:null
        });
        this.submit = this.submit.bind(this);
        this.changeInput = this.changeInput.bind(this);
    }
    changeInput(e)
    {
        if(e.target.name=="Postcode")
        {
            this.setState({
                [e.target.name]:e.target.value
            });
            return null;
        }
        this.setState({
            [e.target.name]:parseInt(Math.round(e.target.value))
        });
    }
    async submit(e)
    {
        e.preventDefault();
        let {Ristretto,Espresso,Lungo} = this.state;
        let data = {
            Ristretto:Ristretto,
            Espresso:Espresso,
            Lungo:Lungo
        };
        const res = await axios.post('/api/v1/cashback',data)
        .then(r=>{
            if(r.data.status)
            {   toast.success(r.data.message);
            }else if(r.data.status==false)
            {
                toast.warn(r.data.error);  
            }
        }).catch(err=>{
            toast.error(err.response.data.message); 
        try {
            let errors  = err.response.data.errors;
            
              Object.keys(errors).forEach(error => {
                toast.warn(errors[error][0]);  
            });
        } catch (error) {
            
        }
        });

    }
    async nearLoc(e)
    {
        e.preventDefault();
        let {Postcode} = this.state;
    
        const res = await axios.get('/api/v1/nearby/'+Postcode)
        .then(r=>{
            if(r.data.status==false)
            {
                toast.warn(r.data.error);  
                return null;
            }
            this.setState({
                nearLocationAdd:r.data.data[0]['address'],
                nearLocationTime:r.data.data[0]['timeTable']
            });
        }).catch(err=>{
            toast.error(err.response.data.message); 
        try {
            let errors  = err.response.data.errors;
            
              Object.keys(errors).forEach(error => {
                toast.warn(errors[error][0]);  
            });
        } catch (error) {
            
        }
        });

    }
  render() {
    const {Ristretto,Espresso,Lungo,Postcode,nearLocationAdd,nearLocationTime} = this.state;
    let time=[];
    if(nearLocationTime!=null)
    {
       Object.keys(nearLocationTime).forEach((el,key)=>{
            if(nearLocationTime[el]==null)
            {
                time.push(<li key={key}>{el} - Close</li>);
            }
            else
            {
                time.push(<li key={key}>{el} - {nearLocationTime[el].open} to {nearLocationTime[el].close}</li>);
            }
       });
    }
    return (
        <div id="app">
            <Container fluid>
            <Row>
                <Col md="4" xm="4" sm="6">
                <Form onSubmit={(e)=>{this.submit(e)}}>
                <FormGroup>
                <Label for="Ristretto">Ristretto</Label>
                <Input
                    type="number"
                    name="Ristretto"
                    id="Ristretto"
                    value={Ristretto}
                    onChange={(e) => {this.changeInput(e)}}
                    placeholder="Ristretto"
                />
                </FormGroup>
                <FormGroup>
                <Label for="Espresso">Espresso</Label>
                <Input
                    type="number"
                    name="Espresso"
                    id="Espresso"
                    value={Espresso}
                    onChange={(e) => {this.changeInput(e)}}
                    placeholder="Espresso"
                />
                </FormGroup>
                <FormGroup>
                <Label for="Lungo">Lungo</Label>
                <Input
                    type="number"
                    name="Lungo"
                    id="Lungo"
                    value={Lungo}
                    onChange={(e) => {this.changeInput(e)}}
                    placeholder="Lungo"
                />
                </FormGroup>
                <FormGroup className="text-center">
                <Button type="btn btn-success">Submit</Button>
                </FormGroup>
                </Form>
                </Col>
                <Col md="4" xm="4" sm="6">
                <Form onSubmit={(e)=>{this.nearLoc(e)}}>
                <FormGroup>
                <Label for="Postcode">Postcode</Label>
                <Input
                    type="text"
                    name="Postcode"
                    id="Postcode"
                    value={Postcode}
                    onChange={(e) => {this.changeInput(e)}}
                    placeholder="Postcode"
                />
                </FormGroup>
                <FormGroup className="text-center">
                <Button type="btn btn-success">Nearest Location</Button>
                </FormGroup>
                <FormGroup className="text-center">
                    {nearLocationAdd!=null && nearLocationAdd}
                    {nearLocationTime!=null && <ul>{time}</ul>}
                </FormGroup>
                </Form>
                </Col>
           
            </Row>
            <ToastContainer />
            </Container>
        </div>
     );
  }
}

if (document.getElementById('app')) {
    ReactDOM.render(<App />, document.getElementById('app'));
}
