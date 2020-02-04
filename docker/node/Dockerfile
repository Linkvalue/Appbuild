# base image
FROM node:12-alpine

# set working directory
RUN mkdir /app
WORKDIR /app

ENV PATH /app/node_modules/.bin:$PATH

# install and cache app dependencies
COPY package.json /app/package.json
RUN npm install

# add app
COPY . /app

CMD ["npm", "start", "--host", "0.0.0.0"]
