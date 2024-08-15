import React from 'react';
import { Input } from './QuoteInputStyled';

const QuoteInput = ({ value, onChange }) => (
  <Input
    type="number"
    value={value}
    onChange={onChange}
    placeholder="Enter number of quotes"
  />
);

export default QuoteInput;
